<?php

use yii\db\Migration;

/**
 * Adds X (Twitter), Threads and AstroBin profile links next to the existing website/facebook/instagram/github ones.
 */
class m261127_120000_add_more_links_to_user_profile extends Migration
{
    public function safeUp()
    {
        $this->addColumn('users', 'twitter', $this->string());
        $this->addColumn('users', 'threads', $this->string());
        $this->addColumn('users', 'astrobin', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('users', 'astrobin');
        $this->dropColumn('users', 'threads');
        $this->dropColumn('users', 'twitter');
    }
}
