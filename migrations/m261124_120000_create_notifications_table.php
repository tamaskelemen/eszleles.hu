<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notifications}}`.
 *
 * A row tells a single user (`user_id`) about a comment; it is unread until `seen_at` is set.
 */
class m261124_120000_create_notifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('notifications', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'comment_id' => $this->integer()->notNull(),
            'type' => $this->string(32)->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
            'seen_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('fk_notifications_user_id_users_id', 'notifications', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_notifications_comment_id_comments_id', 'notifications', 'comment_id', 'comments', 'id', 'CASCADE', 'CASCADE');

        // A user is notified about a comment only once, no matter how many reasons there are for it.
        $this->createIndex('idx_notifications_user_id_comment_id', 'notifications', ['user_id', 'comment_id'], true);
        $this->createIndex('idx_notifications_user_id_seen_at', 'notifications', ['user_id', 'seen_at']);
        $this->createIndex('idx_notifications_comment_id', 'notifications', 'comment_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('notifications');
    }
}
