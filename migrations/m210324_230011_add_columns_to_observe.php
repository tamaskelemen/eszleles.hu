<?php

use yii\db\Migration;

/**
 * Class m210324_230011_add_columns_to_observe
 */
class m210324_230011_add_columns_to_observe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('observations', 'mechanics', $this->string());
        $this->addColumn('observations', 'moon_phase', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observations', 'moon_phase');
        $this->dropColumn('observations', 'mechanics');
    }
}
