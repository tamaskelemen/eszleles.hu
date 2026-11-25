<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_badges}}`.
 *
 * The earned badges, written by BadgeSync only. It can be rebuilt any time by `php yii badges/sync`.
 */
class m261125_220000_create_user_badges_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_badges', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'category' => $this->string(16)->notNull(),
            'threshold' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'tier' => $this->smallInteger()->notNull(),
            'earned_at' => $this->timestamp(),
        ]);

        $this->addForeignKey('fk_user_badges_user_id_users_id', 'user_badges', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('idx_user_badges_user_id_category_threshold', 'user_badges', ['user_id', 'category', 'threshold'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_badges');
    }
}
