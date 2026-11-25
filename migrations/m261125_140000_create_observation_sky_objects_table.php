<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%observation_sky_objects}}`.
 *
 * Which sky objects an observation is about. Rejected rows are kept, so the matcher does not add them again.
 */
class m261125_140000_create_observation_sky_objects_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('observation_sky_objects', [
            'id' => $this->primaryKey(),
            'observation_id' => $this->integer()->notNull(),
            'sky_object_id' => $this->integer()->notNull(),
            'designation' => $this->string(32),
            'status' => $this->string(16)->notNull(),
            'reason' => $this->string(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addForeignKey('fk_observation_sky_objects_observation_id_observations_id', 'observation_sky_objects', 'observation_id', 'observations', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_observation_sky_objects_sky_object_id_sky_objects_id', 'observation_sky_objects', 'sky_object_id', 'sky_objects', 'id', 'RESTRICT', 'CASCADE');

        $this->createIndex('idx_observation_sky_objects_observation_id_sky_object_id', 'observation_sky_objects', ['observation_id', 'sky_object_id'], true);
        $this->createIndex('idx_observation_sky_objects_sky_object_id_status', 'observation_sky_objects', ['sky_object_id', 'status']);

        // set by an admin, e.g. the title is "Hold" in the deep sky category
        $this->addColumn('observations', 'has_no_sky_object', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observations', 'has_no_sky_object');
        $this->dropTable('observation_sky_objects');
    }
}
