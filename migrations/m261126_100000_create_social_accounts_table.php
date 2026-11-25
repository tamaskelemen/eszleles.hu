<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%social_accounts}}`.
 *
 * The external (Google, ...) accounts the users can log in with.
 */
class m261126_100000_create_social_accounts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('social_accounts', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'provider' => $this->string(32)->notNull(),
            'provider_user_id' => $this->string()->notNull(),
            'email' => $this->string(),
            'name' => $this->string(),
            'picture_url' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_social_accounts_user_id_users_id', 'social_accounts', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('idx_social_accounts_provider_provider_user_id', 'social_accounts', ['provider', 'provider_user_id'], true);
        $this->createIndex('idx_social_accounts_user_id_provider', 'social_accounts', ['user_id', 'provider'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('social_accounts');
    }
}
