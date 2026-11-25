<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sky_list_items}}`.
 *
 * The objects of the observing programs (see SkyPrograms), filled by `php yii sky/import`. An item may have more objects
 * (C 14 is NGC 869 and NGC 884), it is done if any of them is observed.
 */
class m261125_200000_create_sky_list_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('sky_list_items', [
            'id' => $this->primaryKey(),
            'list' => $this->string(16)->notNull(),
            'number' => $this->integer()->notNull(),
            'sky_object_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_sky_list_items_sky_object_id_sky_objects_id', 'sky_list_items', 'sky_object_id', 'sky_objects', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('idx_sky_list_items_list_number_sky_object_id', 'sky_list_items', ['list', 'number', 'sky_object_id'], true);
        $this->createIndex('idx_sky_list_items_sky_object_id', 'sky_list_items', 'sky_object_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('sky_list_items');
    }
}
