<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%image_versions}}`.
 *
 * An observation can have multiple image versions; the latest one is the main image.
 * Every version owns an original and a thumbnail row in `images`.
 */
class m261029_120000_create_image_versions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('image_versions', [
            'id' => $this->primaryKey(),
            'observe_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_image_versions_observe_id_observations_id', 'image_versions', 'observe_id', 'observations', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_image_versions_observe_id', 'image_versions', 'observe_id');

        $this->addColumn('images', 'image_version_id', $this->integer());
        $this->addForeignKey('fk_images_image_version_id_image_versions_id', 'images', 'image_version_id', 'image_versions', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_images_image_version_id', 'images', 'image_version_id');

        // Every observation that already has images gets a single (first) version.
        $this->execute("
            INSERT INTO image_versions (observe_id, created_at)
            SELECT i.observe_id, MIN(i.created_at)
            FROM images i
            JOIN observations o ON o.id = i.observe_id
            GROUP BY i.observe_id
        ");
        $this->execute("
            UPDATE images i
            SET image_version_id = v.id
            FROM image_versions v
            WHERE v.observe_id = i.observe_id
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_images_image_version_id_image_versions_id', 'images');
        $this->dropColumn('images', 'image_version_id');
        $this->dropTable('image_versions');
    }
}
