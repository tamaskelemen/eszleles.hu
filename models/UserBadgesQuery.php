<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[UserBadge]].
 *
 * @see UserBadge
 */
class UserBadgesQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return UserBadge[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return UserBadge|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int $id
     * @return UserBadgesQuery
     */
    public function ofUser($id)
    {
        return $this->andWhere(['user_id' => $id]);
    }
}
