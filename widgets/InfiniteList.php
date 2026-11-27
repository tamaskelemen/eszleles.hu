<?php

namespace app\widgets;

use app\assets\AppAsset;
use app\components\feed\FeedSource;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * A list of a FeedSource that loads the next batch as it is scrolled to the bottom (web/js/infinite-scroll.js).
 *
 * The first batch ($first) is rendered here, the next ones come from the action at $url, which sets their size, which returns InfiniteList::respond().
 * The action gets the cursor in `utana` and `osszes=1` when everything else is asked for.
 */
class InfiniteList extends Widget
{
    /** @var FeedSource */
    public $source;

    /** @var array|string route of the action that returns the next batches */
    public $url;

    /** @var int size of the first batch */
    public $first = 20;

    /** @var int|null at most this many batches are loaded after the first one, null for no limit */
    public $maxLoads;

    /** @var int the next batch starts loading this many pixels before the end of the list is in view */
    public $preload = 0;

    /** @var bool whether there is a button to load all the rest */
    public $loadAll = false;

    /** @var array HTML options of the list */
    public $listOptions = ['class' => 'obs-grid'];

    /** @var array HTML options of an item */
    public $itemOptions = ['class' => 'obs-cell'];

    public function init()
    {
        parent::init();

        if (!$this->source instanceof FeedSource || $this->url === null) {
            throw new InvalidConfigException('InfiniteList needs a source and a url.');
        }
    }

    public function run()
    {
        $page = $this->source->fetch(null, $this->first);
        $loadsLeft = $this->maxLoads;

        $view = $this->getView();
        $view->registerJsFile('/js/infinite-scroll.js?v=' . filemtime(Yii::getAlias('@webroot/js/infinite-scroll.js')), [
            'depends' => AppAsset::class,
        ]);

        $hasMore = $page->next !== null && ($loadsLeft === null || $loadsLeft > 0);

        $html = Html::tag('div', self::renderItems($this->source, $view, $page->items, $this->itemOptions), array_merge(
            $this->listOptions,
            ['data-infinite-items' => true]
        ));

        if ($hasMore) {
            $html .= Html::tag('div', '', ['class' => 'infinite__sentinel', 'data-infinite-sentinel' => true]);
        }

        $html .= Html::tag('p', '', ['class' => 'infinite__status', 'role' => 'status', 'data-infinite-status' => true]);

        if ($hasMore) {
            $buttons = Html::button('Továbbiak betöltése', [
                'class' => 'btn btn-outline',
                'data-infinite-more' => true,
                'hidden' => true,
            ]);
            if ($this->loadAll) {
                $buttons .= Html::button('Összes betöltése', ['class' => 'btn btn-outline', 'data-infinite-all' => true]);
            }
            $html .= Html::tag('div', $buttons, ['class' => 'infinite__actions']);
        }

        return Html::tag('div', $html, [
            'id' => $this->getId(),
            'class' => 'infinite',
            'data' => [
                'infinite' => true,
                'url' => Url::to($this->url),
                'cursor' => $hasMore ? $page->next : '',
                'loads-left' => $loadsLeft === null ? '' : $loadsLeft,
                'preload' => (int)$this->preload,
            ],
        ]);
    }

    /**
     * The answer of the action that gives the next batches.
     *
     * @param FeedSource $source
     * @param string|null $cursor
     * @param int|null $limit null for all the rest
     * @param array $itemOptions the same as of the widget
     * @return array to return as JSON
     */
    public static function respond(FeedSource $source, $cursor, $limit, $itemOptions = ['class' => 'obs-cell'])
    {
        $page = $source->fetch($cursor, $limit);

        return [
            'html' => self::renderItems($source, Yii::$app->getView(), $page->items, $itemOptions),
            'count' => count($page->items),
            'next' => $page->next,
        ];
    }

    /**
     * @param FeedSource $source
     * @param View $view
     * @param array $items
     * @param array $itemOptions
     * @return string
     */
    private static function renderItems(FeedSource $source, View $view, array $items, array $itemOptions)
    {
        $html = '';
        foreach ($items as $item) {
            $html .= Html::tag('div', $source->renderItem($view, $item), $itemOptions);
        }
        return $html;
    }
}
