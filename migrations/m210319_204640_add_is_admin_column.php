<?php

use yii\db\Migration;

/**
 * Class m210319_204640_add_is_admin_column
 */
class m210319_204640_add_is_admin_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'is_admin', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'is_admin');
    }
}
