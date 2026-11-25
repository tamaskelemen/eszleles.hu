<?php

namespace app\models;

/**
 * This is the model class for table "social_accounts".
 *
 * An external account the user can log in with, see SocialLogin.
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider id of the auth client
 * @property string $provider_user_id id of the user at the provider
 * @property string|null $email
 * @property string|null $name
 * @property string|null $picture_url refreshed on every login
 * @property string $created_at
 *
 * @property User $user
 */
class SocialAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'social_accounts';
    }

    /**
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return SocialAccountsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SocialAccountsQuery(get_called_class());
    }
}
