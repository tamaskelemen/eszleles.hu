<?php

use yii\db\Migration;

/**
 * Handles the creation of tables `{{%sky_objects}}` and `{{%sky_object_aliases}}`.
 *
 * The catalog of the sky map, filled by `php yii sky/import`. An alias is a key built from a catalog number or a
 * nickname (see SkyMatcher), a nickname may belong to more than one object.
 */
class m261125_100000_create_sky_objects_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('sky_objects', [
            'id' => $this->primaryKey(),
            'name' => $this->string(32)->notNull()->unique(),
            'messier' => $this->integer(),
            'common_name' => $this->string(),
            'type' => $this->string(16)->notNull(),
            'constellation' => $this->string(3)->notNull(),
            'ra' => $this->double()->notNull(),
            'dec' => $this->double()->notNull(),
            'size' => $this->double(),
        ]);

        $this->createTable('sky_object_aliases', [
            'id' => $this->primaryKey(),
            'sky_object_id' => $this->integer()->notNull(),
            'alias' => $this->string()->notNull(),
        ]);

        $this->addForeignKey('fk_sky_object_aliases_sky_object_id_sky_objects_id', 'sky_object_aliases', 'sky_object_id', 'sky_objects', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('idx_sky_object_aliases_alias_sky_object_id', 'sky_object_aliases', ['alias', 'sky_object_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('sky_object_aliases');
        $this->dropTable('sky_objects');
    }
}
