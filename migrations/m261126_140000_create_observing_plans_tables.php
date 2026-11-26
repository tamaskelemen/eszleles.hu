<?php

use yii\db\Migration;

/**
 * Handles the creation of tables `{{%observing_plans}}` and `{{%observing_plan_items}}`.
 *
 * Observing plans: lists of sky objects made by the users, like the built-in observing programs.
 */
class m261126_140000_create_observing_plans_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('observing_plans', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'is_public' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_observing_plans_user_id_users_id', 'observing_plans', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_observing_plans_user_id', 'observing_plans', 'user_id');
        $this->createIndex('idx_observing_plans_is_public', 'observing_plans', 'is_public');

        $this->createTable('observing_plan_items', [
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer()->notNull(),
            'sky_object_id' => $this->integer()->notNull(),
            'designation' => $this->string(),
            'position' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_observing_plan_items_plan_id_observing_plans_id', 'observing_plan_items', 'plan_id', 'observing_plans', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_observing_plan_items_sky_object_id_sky_objects_id', 'observing_plan_items', 'sky_object_id', 'sky_objects', 'id', 'RESTRICT', 'CASCADE');
        $this->createIndex('idx_observing_plan_items_plan_id_sky_object_id', 'observing_plan_items', ['plan_id', 'sky_object_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('observing_plan_items');
        $this->dropTable('observing_plans');
    }
}
