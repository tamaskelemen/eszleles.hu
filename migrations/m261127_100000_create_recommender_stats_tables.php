<?php

use yii\db\Migration;

/**
 * Daily usage counters of the recommender, shown on the admin pages.
 */
class m261127_100000_create_recommender_stats_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // how many times something happened on a day, separately for guests and users
        $this->createTable('recommender_stats', [
            'day' => $this->date()->notNull(),
            'event' => $this->string(32)->notNull(),
            'is_guest' => $this->boolean()->notNull(),
            'count' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->addPrimaryKey('pk_recommender_stats', 'recommender_stats', ['day', 'event', 'is_guest']);

        // the users who used it on a day, for the distinct count
        $this->createTable('recommender_user_days', [
            'day' => $this->date()->notNull(),
            'user_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_recommender_user_days', 'recommender_user_days', ['day', 'user_id']);
        $this->addForeignKey('fk_recommender_user_days_user', 'recommender_user_days', 'user_id', 'users', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('recommender_user_days');
        $this->dropTable('recommender_stats');
    }
}
