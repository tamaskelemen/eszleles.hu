<?php

use yii\db\Migration;

/**
 * Class m210327_183455_add_size_to_images
 */
class m210327_183455_add_size_to_images extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('images', 'size', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('images', 'size');
    }

}
