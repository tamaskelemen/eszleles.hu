<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[SocialAccount]].
 *
 * @see SocialAccount
 */
class SocialAccountsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return SocialAccount[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return SocialAccount|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int $id
     * @return SocialAccountsQuery
     */
    public function ofUser($id)
    {
        return $this->andWhere(['user_id' => $id]);
    }

    /**
     * @param string $provider
     * @return SocialAccountsQuery
     */
    public function ofProvider($provider)
    {
        return $this->andWhere(['provider' => $provider]);
    }

    /**
     * @param string $id
     * @return SocialAccountsQuery
     */
    public function ofProviderUser($id)
    {
        return $this->andWhere(['provider_user_id' => $id]);
    }
}
