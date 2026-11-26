<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ObservingPlanItem]].
 *
 * @see ObservingPlanItem
 */
class ObservingPlanItemsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return ObservingPlanItem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ObservingPlanItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int $id
     * @return ObservingPlanItemsQuery
     */
    public function ofPlan($id)
    {
        return $this->andWhere(['observing_plan_items.plan_id' => $id]);
    }
}
