<?php

use yii\db\Migration;

/**
 * The uploaded profile picture of the user, a square cropped image under web/uploads/avatars.
 */
class m261126_120000_add_avatar_path_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'avatar_path', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'avatar_path');
    }
}
