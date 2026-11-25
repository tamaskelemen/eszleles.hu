<?php
namespace app\widgets;

use app\models\Observe;
use yii\widgets\ActiveField;

/**
 * Form field of an observation, the label is followed by an info icon if the attribute has a help text.
 */
class ObserveField extends ActiveField
{
    /**
     * {@inheritdoc}
     */
    public function label($label = null, $options = [])
    {
        parent::label($label, $options);

        $helpText = Observe::getAttribteHelpText($this->attribute);

        if ($label !== false && $helpText) {
            $this->parts['{label}'] .= ' ' . $this->form->view->render('//_common-items/_infoTip', [
                'label' => 'Súgó: ' . $this->model->getAttributeLabel($this->attribute),
                'text' => $helpText,
            ]);
        }

        return $this;
    }
}
