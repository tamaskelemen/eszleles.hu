<?php

use yii\db\Migration;

/**
 * Class m220213_101615_add_eclipse_fields_to_obs
 */
class m220213_101615_add_eclipse_fields_to_obs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("observations", 'duration', $this->string());
        $this->addColumn("observations", 'coverage', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observations', 'coverage');
        $this->dropColumn('observations', 'duration');
    }
}
