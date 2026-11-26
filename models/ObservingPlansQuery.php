<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ObservingPlan]].
 *
 * @see ObservingPlan
 */
class ObservingPlansQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return ObservingPlan[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ObservingPlan|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int $id
     * @return ObservingPlansQuery
     */
    public function ofId($id)
    {
        return $this->andWhere(['observing_plans.id' => $id]);
    }

    /**
     * @param int $id
     * @return ObservingPlansQuery
     */
    public function ofUser($id)
    {
        return $this->andWhere(['observing_plans.user_id' => $id]);
    }

    /**
     * @return ObservingPlansQuery
     */
    public function isPublic()
    {
        return $this->andWhere(['observing_plans.is_public' => true]);
    }
}
