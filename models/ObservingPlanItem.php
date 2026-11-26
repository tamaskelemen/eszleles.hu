<?php

namespace app\models;

/**
 * This is the model class for table "observing_plan_items".
 *
 * A sky object of an observing plan.
 *
 * @property int $id
 * @property int $plan_id
 * @property int $sky_object_id
 * @property string|null $designation the catalog number it was added by, if the object is known by another one
 * @property int $position order in the plan
 * @property string $created_at
 *
 * @property ObservingPlan $plan
 * @property SkyObject $skyObject
 */
class ObservingPlanItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'observing_plan_items';
    }

    /**
     * @return \yii\db\ActiveQuery|ObservingPlansQuery
     */
    public function getPlan()
    {
        return $this->hasOne(ObservingPlan::class, ['id' => 'plan_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSkyObject()
    {
        return $this->hasOne(SkyObject::class, ['id' => 'sky_object_id']);
    }

    /**
     * {@inheritdoc}
     * @return ObservingPlanItemsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ObservingPlanItemsQuery(get_called_class());
    }
}
