<?php

use yii\db\Migration;

/**
 * Where the profile picture comes from: "upload", the id of an auth client (e.g. "google"), or "removed" if the user
 * deleted it. The picture of an external account is copied only if the user did not upload or delete one.
 */
class m261126_130000_add_avatar_source_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'avatar_source', $this->string(32));
        // the address of the external picture the copy was made from, it is downloaded again only if it changes
        $this->addColumn('users', 'avatar_source_url', $this->text());
        $this->update('users', ['avatar_source' => 'upload'], ['not', ['avatar_path' => null]]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('users', 'avatar_source_url');
        $this->dropColumn('users', 'avatar_source');
    }
}
