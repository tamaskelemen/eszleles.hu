<?php
namespace app\widgets;

use app\components\SkyMatcher;
use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JqueryAsset;

/**
 * List of the sky objects an observation is about. It follows the title while it is typed, if there is an input for it.
 * Without JavaScript nothing is posted, so the server matches the title alone.
 */
class SkyObjectInput extends Widget
{
    /** @var \yii\base\Model Observe or a form */
    public $model;

    /** @var bool whether the title is edited on the same form */
    public $followsTitle = true;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->view->registerJsFile('/js/sky-objects.js?v=' . filemtime(Yii::getAlias('@webroot/js/sky-objects.js')), [
            'depends' => [JqueryAsset::class],
        ]);

        return $this->render('skyObjectInput', [
            'id' => $this->getId(),
            'options' => [
                'id' => $this->getId(),
                'class' => 'sky-objects form-group',
                'hidden' => true,
                'data-items' => Json::encode($this->getItems()),
                'data-dismissed' => Json::encode($this->getDismissedIds()),
                'data-items-name' => Html::getInputName($this->model, 'skyObjectItems') . '[]',
                'data-title-name' => Html::getInputName($this->model, 'skyObjectTitle'),
                'data-title' => (string)$this->model->object_name,
                'data-title-input' => $this->followsTitle ? '#' . Html::getInputId($this->model, 'object_name') : null,
                'data-match-url' => Url::to(['/sky/match']),
                'data-search-url' => Url::to(['/sky/search']),
            ],
        ]);
    }

    /**
     * @return array
     */
    private function getItems()
    {
        $matched = array_column($this->getMatchedObjects(), null, 'id');
        $items = [];

        // the form was posted, but there was an error
        if ($this->model->skyObjectTitle !== null) {
            $chosen = SkyObject::findItems((array)$this->model->skyObjectItems);

            foreach (SkyObject::find()->where(['id' => array_keys($chosen)])->asArray()->all() as $object) {
                $items[] = SkyObject::buildItem(['designation' => $chosen[$object['id']]] + $object);
            }
        } else if ($this->model instanceof Observe) {
            $links = array_column($this->model->skyObjectLinks, null, 'sky_object_id');

            foreach ($links as $link) {
                if ($link->status !== ObservationSkyObject::STATUS_REJECTED) {
                    $items[] = SkyObject::buildItem(['designation' => $link->designation] + $link->skyObject->attributes);
                }
            }

            // found, but not stored yet, saving the form would reject them if they were not listed
            if (!$this->model->has_no_sky_object) {
                $items = array_merge($items, array_map([SkyObject::class, 'buildItem'], array_values(array_diff_key($matched, $links))));
            }
        }

        foreach ($items as &$item) {
            // these are replaced when the title changes
            $item['isMatched'] = isset($matched[$item['id']]);
        }

        return $items;
    }

    /**
     * @return int[] objects in the title that were removed by hand
     */
    private function getDismissedIds()
    {
        $ids = [];

        if ($this->model->skyObjectTitle !== null) {
            $ids = array_diff(array_column($this->getMatchedObjects(), 'id'), array_column($this->getItems(), 'id'));
        } else if ($this->model instanceof Observe) {
            foreach ($this->model->skyObjectLinks as $link) {
                if ($link->status === ObservationSkyObject::STATUS_REJECTED) {
                    $ids[] = $link->sky_object_id;
                }
            }
        }

        return array_map('intval', array_values($ids));
    }

    private function getMatchedObjects()
    {
        $matcher = new SkyMatcher([SkyObject::class, 'findByAliases']);
        $title = $this->model->skyObjectTitle ?? $this->model->object_name;

        return $matcher->match((string)$title)['objects'];
    }
}
