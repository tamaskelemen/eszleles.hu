<?php

use yii\db\Migration;

/**
 * Class m210330_181000_add_links_to_user_profile
 */
class m210330_181000_add_links_to_user_profile extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'facebook', $this->string());
        $this->addColumn('users', 'instagram', $this->string());
        $this->addColumn('users', 'website', $this->string());
        $this->addColumn('users', 'github', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'github');
        $this->dropColumn('users', 'facebook');
        $this->dropColumn('users', 'instagram');
        $this->dropColumn('users', 'website');
    }

}
