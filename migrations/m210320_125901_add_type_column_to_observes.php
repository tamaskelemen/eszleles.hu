<?php

use yii\db\Migration;

/**
 * Class m210320_125901_add_type_column_to_observes
 */
class m210320_125901_add_type_column_to_observes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('observations', 'type', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observations', 'type');
    }

}
