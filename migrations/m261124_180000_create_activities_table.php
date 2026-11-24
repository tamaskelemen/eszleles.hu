<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%activities}}`.
 *
 * A row is a single thing a user did (or someone tried to do in the name of the user), `type` tells what.
 */
class m261124_180000_create_activities_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('activities', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->string(32)->notNull(),
            'ip' => $this->string(45),
            'user_agent' => $this->string(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_activities_user_id_users_id', 'activities', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('idx_activities_user_id_id', 'activities', ['user_id', 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('activities');
    }
}
