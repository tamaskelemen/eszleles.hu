<?php

use yii\db\Migration;

/**
 * Class m210330_171354_add_meteor_columns_to_obs
 */
class m210330_171354_add_meteor_columns_to_obs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('observations', 'meteor_membership', $this->string());
        $this->addColumn('observations', 'brightness', $this->float());
        $this->addColumn('observations', 'color', $this->string());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observations', 'meteor_membership');
        $this->dropColumn('observations', 'brightness');
        $this->dropColumn('observations', 'color');
    }


}
