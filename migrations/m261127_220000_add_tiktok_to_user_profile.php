<?php

use yii\db\Migration;

/**
 * Adds the TikTok profile link next to the other ones.
 */
class m261127_220000_add_tiktok_to_user_profile extends Migration
{
    public function safeUp()
    {
        $this->addColumn('users', 'tiktok', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('users', 'tiktok');
    }
}
