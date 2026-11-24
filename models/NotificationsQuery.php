<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Notification]].
 *
 * @see Notification
 */
class NotificationsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Notification[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Notification|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id integer
     * @return NotificationsQuery
     */
    public function ofUser($id)
    {
        return $this->andWhere(['user_id' => $id]);
    }

    /**
     * @return NotificationsQuery
     */
    public function unseen()
    {
        return $this->andWhere(['seen_at' => null]);
    }
}
