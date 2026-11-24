<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Activity]].
 *
 * @see Activity
 */
class ActivitiesQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Activity[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Activity|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id integer
     * @return ActivitiesQuery
     */
    public function ofUser($id)
    {
        return $this->andWhere(['activities.user_id' => $id]);
    }
}
