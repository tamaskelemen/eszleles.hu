<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ImageTag]].
 *
 * @see ImageTag
 */
class ImageTagsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ImageTag[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ImageTag|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
