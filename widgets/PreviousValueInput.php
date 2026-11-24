<?php
namespace app\widgets;

use app\models\Observe;
use Yii;
use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\widgets\InputWidget;

/**
 * Text input with a dropdown of the values the logged in user has already used in the same field.
 */
class PreviousValueInput extends InputWidget
{
    /** @var array previous values by attribute, shared by every input on the page */
    private static $previousValues;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        Html::addCssClass($this->options, 'form-control');
        // the browser's own suggestions would cover the dropdown
        $this->options += ['autocomplete' => 'off'];
        $input = Html::activeTextInput($this->model, $this->attribute, $this->options);

        $values = $this->getPreviousValues();

        if (empty($values)) {
            return $input;
        }

        // depending on AppAsset here would load site.css before the stylesheets of the other widgets of the form
        $this->view->registerJsFile('/js/previous-values.js?v=' . filemtime(Yii::getAlias('@webroot/js/previous-values.js')), [
            'depends' => [JqueryAsset::class],
        ]);

        return Html::tag('div', $input . $this->renderDropdown($values), ['class' => 'input-group previous-values']);
    }

    /**
     * @param string[] $values
     * @return string
     */
    private function renderDropdown($values)
    {
        $buttonId = $this->options['id'] . '-previous';
        $label = 'Korábbi értékek: ' . $this->model->getAttributeLabel($this->attribute);

        $items = '';
        foreach ($values as $value) {
            $items .= Html::button(Html::encode($value), ['class' => 'dropdown-item', 'data-value' => $value]);
        }

        $button = Html::button('', [
            'id' => $buttonId,
            'class' => 'btn btn-outline dropdown-toggle',
            'title' => 'Korábbi értékek',
            'aria-label' => $label,
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false',
            'data-toggle' => 'dropdown',
        ]);
        $menu = Html::tag('div', $items, ['class' => 'dropdown-menu dropdown-menu-right', 'aria-labelledby' => $buttonId]);

        return Html::tag('div', $button . $menu, ['class' => 'input-group-append']);
    }

    /**
     * @return string[]
     */
    private function getPreviousValues()
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        if (self::$previousValues === null) {
            self::$previousValues = Observe::getPreviousValues(Yii::$app->user->id);
        }

        return self::$previousValues[$this->attribute] ?? [];
    }
}
