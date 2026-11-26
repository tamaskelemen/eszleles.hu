<?php

namespace app\widgets;

use Closure;
use Yii;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\grid\Column;
use yii\grid\DataColumn;
use yii\grid\SerialColumn;
use yii\helpers\Html;

/**
 * GridView that turns its rows into cards on phones (below 768px, styles in `web/css/site.css`, `.grid-view--stack`).
 *
 * On phones the header row is hidden, the filters go under a "Szűrés" button and the sorting into a "Rendezés" dropdown.
 * Every column can say how it is shown in the card with the `mobile` key of its config:
 *
 * - `label` (default): its own line, with the column name in front of it
 * - `title`: the first line of the card, in bold
 * - `meta`: small text after the title, the meta columns are in one line, separated by dots (keep them short)
 * - `note`: small text in its own line, after the meta line
 * - `media`: picture on the left side of the card
 * - `select`: checkbox in the top left corner (default of [[CheckboxColumn]])
 * - `actions`: buttons at the bottom of the card (default of [[ActionColumn]])
 * - `hidden`: not shown on phones (default of [[SerialColumn]])
 *
 * The layout may contain `{toolbar}` for the phone-only filter button and sort dropdown,
 * otherwise it is put in front of `{items}`.
 */
class GridView extends \yii\grid\GridView
{
    const MOBILE_LABEL = 'label';
    const MOBILE_TITLE = 'title';
    const MOBILE_META = 'meta';
    const MOBILE_NOTE = 'note';
    const MOBILE_MEDIA = 'media';
    const MOBILE_SELECT = 'select';
    const MOBILE_ACTIONS = 'actions';
    const MOBILE_HIDDEN = 'hidden';

    public function init()
    {
        $roles = [];
        foreach ($this->columns as $i => $column) {
            if (is_array($column) && array_key_exists('mobile', $column)) {
                $roles[$i] = $column['mobile'];
                unset($this->columns[$i]['mobile']);
            }
        }

        parent::init();

        Html::addCssClass($this->options, 'grid-view--stack');

        if (strpos($this->layout, '{toolbar}') === false) {
            $this->layout = str_replace('{items}', "{toolbar}\n{items}", $this->layout);
        }

        foreach ($this->columns as $i => $column) {
            $this->initMobileColumn($column, $roles[$i] ?? $this->getDefaultRole($column));
        }
    }

    public function renderSection($name)
    {
        if ($name === '{toolbar}') {
            return $this->renderToolbar();
        }

        return parent::renderSection($name);
    }

    /**
     * Same as the parent, but the cells get the column name as `data-label`
     * and the cells without a filter are marked, so they can be left out on phones.
     */
    public function renderFilters()
    {
        if ($this->filterModel === null) {
            return '';
        }

        $cells = [];
        foreach ($this->columns as $column) {
            /** @var Column $column */
            $content = self::invoke($column, 'renderFilterCellContent');
            $options = $column->filterOptions;

            if (strpos($content, '<input') === false && strpos($content, '<select') === false) {
                Html::addCssClass($options, 'grid-filter--empty');
            } else {
                $options['data-label'] = $options['data-label'] ?? $this->getColumnLabel($column);
            }

            $cells[] = Html::tag('td', $content, $options);
        }

        return Html::tag('tr', implode('', $cells), $this->filterRowOptions);
    }

    protected function renderToolbar()
    {
        $hasFilters = $this->filterModel !== null && $this->filterPosition !== self::FILTER_POS_FOOTER;
        $sortOptions = $this->getSortOptions();

        if (!$hasFilters && !$sortOptions) {
            return '';
        }

        $id = $this->options['id'];
        $isFiltering = $hasFilters && $this->isFiltered();
        $parts = [];

        if ($hasFilters) {
            $parts[] = Html::button('Szűrés', [
                'class' => 'btn btn-outline btn-sm grid-toolbar__filter',
                'aria-expanded' => $isFiltering ? 'true' : 'false',
                'aria-controls' => $this->filterRowOptions['id'],
            ]);

            if ($isFiltering) {
                Html::addCssClass($this->options, 'is-filtering');
            }
        }

        if ($sortOptions) {
            $selectId = "$id-sort";
            $parts[] = Html::tag('div',
                Html::label('Rendezés', $selectId, ['class' => 'grid-toolbar__label'])
                . Html::dropDownList(null, $this->getCurrentSortUrl(), $sortOptions, [
                    'id' => $selectId,
                    'class' => 'form-control form-control-sm',
                    'prompt' => $this->getCurrentSortUrl() === null ? '' : null,
                ]),
                ['class' => 'grid-toolbar__sort']
            );
        }

        $this->getView()->registerJs('(function () {
    var grid = $("#' . $id . '");
    grid.on("click", ".grid-toolbar__filter", function () {
        var isOpen = grid.toggleClass("is-filtering").hasClass("is-filtering");
        $(this).attr("aria-expanded", isOpen ? "true" : "false");
    });
    grid.on("change", ".grid-toolbar__sort select", function () {
        if (this.value) {
            window.location.href = this.value;
        }
    });
})();');

        return Html::tag('div', implode("\n", $parts), ['class' => 'grid-toolbar d-md-none']);
    }

    private function initMobileColumn(Column $column, $role)
    {
        $extra = ['class' => "grid-cell--$role"];
        $label = $this->getColumnLabel($column);
        if ($label !== '') {
            $extra['data-label'] = $label;
        }

        $options = $column->contentOptions;
        if ($options instanceof Closure) {
            $column->contentOptions = function ($model, $key, $index, $column) use ($options, $extra) {
                return self::mergeOptions((array)$options($model, $key, $index, $column), $extra);
            };
        } else {
            $column->contentOptions = self::mergeOptions($options, $extra);
        }
    }

    private function getDefaultRole(Column $column)
    {
        if ($column instanceof ActionColumn) {
            return self::MOBILE_ACTIONS;
        }
        if ($column instanceof CheckboxColumn) {
            return self::MOBILE_SELECT;
        }
        if ($column instanceof SerialColumn) {
            return self::MOBILE_HIDDEN;
        }

        return self::MOBILE_LABEL;
    }

    private function getColumnLabel(Column $column)
    {
        $label = $column instanceof DataColumn ? (string)self::invoke($column, 'getHeaderCellLabel') : '';

        if ($column->header !== null) {
            $label = trim(html_entity_decode(strip_tags($column->header))) ?: $label;
        }

        return trim($label);
    }

    /**
     * @return array sort URL => label, both directions of every sortable column
     */
    private function getSortOptions()
    {
        $sort = $this->dataProvider->getSort();
        if ($sort === false) {
            return [];
        }

        $options = [];
        foreach ($this->columns as $column) {
            if (!$column instanceof DataColumn || $column->attribute === null || !$column->enableSorting
                || !$sort->hasAttribute($column->attribute)) {
                continue;
            }

            $label = $this->getColumnLabel($column);
            $options[$this->createSortUrl($column->attribute)] = "$label (növekvő)";
            $options[$this->createSortUrl('-' . $column->attribute)] = "$label (csökkenő)";
        }

        return $options;
    }

    private function getCurrentSortUrl()
    {
        $sort = $this->dataProvider->getSort();
        $orders = $sort ? $sort->getAttributeOrders() : [];

        if (!$orders) {
            return null;
        }

        $attribute = array_key_first($orders);

        return $this->createSortUrl(($orders[$attribute] === SORT_DESC ? '-' : '') . $attribute);
    }

    /**
     * Same as [[\yii\data\Sort::createUrl()]], but with the given direction and from the first page.
     */
    private function createSortUrl($value)
    {
        $sort = $this->dataProvider->getSort();
        $params = $sort->params === null ? Yii::$app->getRequest()->getQueryParams() : $sort->params;
        $params[$sort->sortParam] = $value;
        $params[0] = $sort->route === null ? Yii::$app->controller->getRoute() : $sort->route;

        $pagination = $this->dataProvider->getPagination();
        if ($pagination !== false) {
            unset($params[$pagination->pageParam]);
        }

        $urlManager = $sort->urlManager === null ? Yii::$app->getUrlManager() : $sort->urlManager;

        return $urlManager->createUrl($params);
    }

    private function isFiltered()
    {
        foreach ($this->columns as $column) {
            if ($column instanceof DataColumn && $column->filter !== false && is_string($column->filterAttribute)
                && $this->filterModel->canGetProperty($column->filterAttribute)) {
                $value = $this->filterModel->{$column->filterAttribute};

                if ($value !== '' && $value !== null && $value !== []) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function mergeOptions(array $options, array $extra)
    {
        Html::addCssClass($options, $extra['class']);
        if (isset($extra['data-label']) && !isset($options['data-label'])) {
            $options['data-label'] = $extra['data-label'];
        }

        return $options;
    }

    /**
     * Calls a protected method of a column.
     */
    private static function invoke($object, $method)
    {
        return (function () use ($method) {
            return $this->$method();
        })->call($object);
    }
}
