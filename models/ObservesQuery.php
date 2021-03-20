<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Observe]].
 *
 * @see Observe
 */
class ObservesQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Observe[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Observe|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id
     * @return ObservesQuery
     */
    public function ofUser($id)
    {
        return $this->where(['observer_id' => $id]);
    }

    /**
     * @param $id integer
     * @return ObservesQuery
     */
    public function ofId($id)
    {
        return $this->where(['id' => $id]);
    }

}
