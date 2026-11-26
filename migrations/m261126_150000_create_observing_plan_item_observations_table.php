<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%observing_plan_item_observations}}`.
 *
 * Observations attached to an object of an observing plan by hand, for the ones that are not identified as that object.
 */
class m261126_150000_create_observing_plan_item_observations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('observing_plan_item_observations', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer()->notNull(),
            'observation_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_plan_item_observations_item_id', 'observing_plan_item_observations', 'item_id', 'observing_plan_items', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_plan_item_observations_observation_id', 'observing_plan_item_observations', 'observation_id', 'observations', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_plan_item_observations_item_id_observation_id', 'observing_plan_item_observations', ['item_id', 'observation_id'], true);
        $this->createIndex('idx_plan_item_observations_observation_id', 'observing_plan_item_observations', 'observation_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('observing_plan_item_observations');
    }
}
