<?php

use yii\db\Migration;

/**
 * Data of the recommender (see components/recommender): brightness, shape and the catalogs of the objects, filled by
 * `php yii sky/import`. The catalogs are the prefixes of the designations (e.g. "NGC", "SH2"), stored with the object so
 * a catalog filter does not have to search the aliases.
 */
class m261126_170000_add_brightness_columns_to_sky_objects extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('sky_objects', 'magnitude', $this->double());
        $this->addColumn('sky_objects', 'surface_brightness', $this->double());
        $this->addColumn('sky_objects', 'min_axis', $this->double());
        $this->addColumn('sky_objects', 'catalogs', 'text[] NOT NULL DEFAULT \'{}\'');

        $this->execute('CREATE INDEX idx_sky_objects_catalogs ON sky_objects USING GIN (catalogs)');
        $this->createIndex('idx_sky_objects_dec', 'sky_objects', 'dec');
        $this->createIndex('idx_sky_objects_magnitude', 'sky_objects', 'magnitude');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_sky_objects_magnitude', 'sky_objects');
        $this->dropIndex('idx_sky_objects_dec', 'sky_objects');
        $this->dropIndex('idx_sky_objects_catalogs', 'sky_objects');

        $this->dropColumn('sky_objects', 'catalogs');
        $this->dropColumn('sky_objects', 'min_axis');
        $this->dropColumn('sky_objects', 'surface_brightness');
        $this->dropColumn('sky_objects', 'magnitude');
    }
}
