<?php

use yii\db\Migration;

/**
 * The place the recommender calculates with for the user, set on the profile. Without it Budapest is used.
 */
class m261126_180000_add_location_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'location_name', $this->string(255));
        $this->addColumn('users', 'location_latitude', $this->double());
        $this->addColumn('users', 'location_longitude', $this->double());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'location_longitude');
        $this->dropColumn('users', 'location_latitude');
        $this->dropColumn('users', 'location_name');
    }
}
