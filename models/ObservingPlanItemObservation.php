<?php

namespace app\models;

/**
 * This is the model class for table "observing_plan_item_observations".
 *
 * An own observation attached to an object of an observing plan by hand, it counts as done for the observer.
 *
 * @property int $id
 * @property int $item_id
 * @property int $observation_id
 * @property string $created_at
 *
 * @property ObservingPlanItem $item
 * @property Observe $observation
 */
class ObservingPlanItemObservation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'observing_plan_item_observations';
    }

    /**
     * @return \yii\db\ActiveQuery|ObservingPlanItemsQuery
     */
    public function getItem()
    {
        return $this->hasOne(ObservingPlanItem::class, ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|ObservesQuery
     */
    public function getObservation()
    {
        return $this->hasOne(Observe::class, ['id' => 'observation_id']);
    }
}
