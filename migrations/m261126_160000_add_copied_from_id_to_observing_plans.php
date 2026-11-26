<?php

use yii\db\Migration;

/**
 * Adds `copied_from_id` to `{{%observing_plans}}`: the plan a copy was made of, so that the copy can credit it, and
 * `copy_count`: how many times the others copied the plan, the deleted copies too.
 */
class m261126_160000_add_copied_from_id_to_observing_plans extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('observing_plans', 'copied_from_id', $this->integer());
        $this->addColumn('observing_plans', 'copy_count', $this->integer()->notNull()->defaultValue(0));
        $this->addForeignKey('fk_observing_plans_copied_from_id_observing_plans_id', 'observing_plans', 'copied_from_id', 'observing_plans', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_observing_plans_copied_from_id_observing_plans_id', 'observing_plans');
        $this->dropColumn('observing_plans', 'copy_count');
        $this->dropColumn('observing_plans', 'copied_from_id');
    }
}
