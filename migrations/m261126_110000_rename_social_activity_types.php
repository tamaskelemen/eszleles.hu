<?php

use yii\db\Migration;

/**
 * Every provider has its own activity types. Google was the only one so far.
 */
class m261126_110000_rename_social_activity_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('activities', ['type' => 'login_google'], ['type' => 'login_social']);
        $this->update('activities', ['type' => 'connect_google'], ['type' => 'social_connect']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('activities', ['type' => 'login_social'], ['type' => 'login_google']);
        $this->update('activities', ['type' => 'social_connect'], ['type' => 'connect_google']);
    }
}
