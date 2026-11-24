<?php

use yii\db\Migration;

/**
 * Adds the column `parent_id` to table `{{%comments}}`.
 *
 * It is the first level comment a reply belongs to, null for the first level comments.
 */
class m261124_160000_add_parent_id_to_comments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('comments', 'parent_id', $this->integer());
        $this->addForeignKey('fk_comments_parent_id_comments_id', 'comments', 'parent_id', 'comments', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_comments_parent_id', 'comments', 'parent_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_comments_parent_id_comments_id', 'comments');
        $this->dropColumn('comments', 'parent_id');
    }
}
