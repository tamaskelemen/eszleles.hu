<?php

namespace app\models\forms;

use app\models\ObservingPlan;
use app\models\ObservingPlanItem;
use app\models\SkyObject;
use Yii;
use yii\base\Model;

/**
 * Creates and updates an observing plan together with its objects.
 */
class ObservingPlanForm extends Model
{
    public $name;
    public $description;
    public $is_public = false;

    /** @var string[]|null posted by sky-objects.js, see SkyObject::findItems() */
    public $skyObjectItems;

    /** @var string|null posted by sky-objects.js only, without JavaScript the objects are left as they are */
    public $skyObjectTitle;

    /** @var ObservingPlan */
    private $plan;

    /**
     * @param ObservingPlan $plan a new or an existing one
     * @param array $config
     */
    public function __construct(ObservingPlan $plan, $config = [])
    {
        $this->plan = $plan;
        $this->name = $plan->name;
        $this->description = $plan->description;
        $this->is_public = (bool)$plan->is_public;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required', 'message' => 'A mezőt kötelező kitölteni!'],
            [['name', 'description'], 'trim'],
            [['name'], 'string', 'max' => 100, 'tooLong' => 'Legfeljebb {max} karakter lehet.'],
            [['description'], 'string', 'max' => 5000, 'tooLong' => 'Legfeljebb {max} karakter lehet.'],
            [['is_public'], 'boolean'],
            [['skyObjectItems'], 'each', 'rule' => ['string']],
            [['skyObjectTitle'], 'string'],
            [['skyObjectItems'], 'validateItemCount'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Név',
            'description' => 'Leírás',
            'is_public' => 'Nyilvános: bárki megnézheti',
        ];
    }

    public function validateItemCount($attribute)
    {
        if (count((array)$this->$attribute) > ObservingPlan::ITEM_LIMIT) {
            $this->addError($attribute, 'Legfeljebb ' . ObservingPlan::ITEM_LIMIT . ' objektum lehet egy tervben.');
        }
    }

    /**
     * @return ObservingPlan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @return array items of the sky object input: the posted ones after an error, otherwise the stored ones
     */
    public function getItems()
    {
        if ($this->skyObjectTitle !== null) {
            $chosen = SkyObject::findItems((array)$this->skyObjectItems, ObservingPlan::ITEM_LIMIT);
        } else {
            $chosen = [];

            // not array_column(): it skips the null designations of the records
            foreach ($this->plan->isNewRecord ? [] : $this->plan->items as $item) {
                $chosen[$item->sky_object_id] = $item->designation;
            }
        }

        $objects = SkyObject::find()->where(['id' => array_keys($chosen)])->indexBy('id')->asArray()->all();
        $items = [];

        foreach ($chosen as $id => $designation) {
            if (isset($objects[$id])) {
                $items[] = SkyObject::buildItem(['designation' => $designation] + $objects[$id]);
            }
        }

        return $items;
    }

    /**
     * @param int $userId the owner of a new plan
     * @return bool
     * @throws \Throwable
     */
    public function save(int $userId)
    {
        if (!$this->validate()) {
            return false;
        }

        $plan = $this->plan;

        if ($plan->isNewRecord) {
            $plan->user_id = $userId;
        }

        $plan->name = $this->name;
        $plan->description = $this->description === '' ? null : $this->description;
        $plan->is_public = (bool)$this->is_public;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$plan->save(false)) {
                $transaction->rollBack();

                return false;
            }

            if ($this->skyObjectTitle !== null) {
                $this->saveItems(SkyObject::findItems((array)$this->skyObjectItems, ObservingPlan::ITEM_LIMIT));
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @param array $chosen designations (or null) indexed by object id, in the order of the plan
     */
    private function saveItems(array $chosen)
    {
        $existing = ObservingPlanItem::find()->ofPlan($this->plan->id)->indexBy('sky_object_id')->all();
        $position = 0;

        foreach ($existing as $id => $item) {
            if (!array_key_exists($id, $chosen)) {
                $item->delete();
            }
        }

        foreach ($chosen as $id => $designation) {
            $item = $existing[$id] ?? new ObservingPlanItem(['plan_id' => $this->plan->id, 'sky_object_id' => $id]);
            $item->designation = $designation;
            $item->position = $position++;
            $item->save(false);
        }
    }
}
