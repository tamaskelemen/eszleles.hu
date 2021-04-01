<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[BugReport]].
 *
 * @see BugReport
 */
class BugReportsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BugReport[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BugReport|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
