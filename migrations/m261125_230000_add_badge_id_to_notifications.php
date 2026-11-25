<?php

use yii\db\Migration;

/**
 * A notification is either about a comment or about an earned badge.
 */
class m261125_230000_add_badge_id_to_notifications extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notifications', 'badge_id', $this->integer()->null());
        $this->alterColumn('notifications', 'comment_id', 'DROP NOT NULL');

        $this->addForeignKey('fk_notifications_badge_id_user_badges_id', 'notifications', 'badge_id', 'user_badges', 'id', 'CASCADE', 'CASCADE');
        $this->addCheck('chk_notifications_subject', 'notifications', 'num_nonnulls(comment_id, badge_id) = 1');

        // a badge belongs to a single user
        $this->createIndex('idx_notifications_badge_id', 'notifications', 'badge_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('notifications', ['not', ['badge_id' => null]]);

        $this->dropCheck('chk_notifications_subject', 'notifications');
        $this->dropColumn('notifications', 'badge_id');
        $this->alterColumn('notifications', 'comment_id', 'SET NOT NULL');
    }
}
