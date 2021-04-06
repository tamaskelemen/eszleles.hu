<?php

use yii\db\Migration;

/**
 * Class m210406_190414_add_additional_fields_for_images
 */
class m210406_190414_add_additional_fields_for_images extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("observations", 'expo', $this->string());
        $this->addColumn("observations", 'filter', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observations', 'expo');
        $this->dropColumn('observations', 'filter');
    }
}
