<?php

use yii\db\Migration;

/**
 * Class m210407_182641_add_intro_to_user_profile
 */
class m210407_182641_add_intro_to_user_profile extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'introduction', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'introduction');
    }
}
