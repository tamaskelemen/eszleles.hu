<?php

use yii\db\Migration;

/**
 * Handles adding column `source` to table `{{%observation_sky_objects}}`.
 *
 * Where the matcher found the object: in the title, or in a tag of the image. Both remove only their own ones.
 */
class m261125_210000_add_source_to_observation_sky_objects extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('observation_sky_objects', 'source', $this->string(8)->notNull()->defaultValue('title'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('observation_sky_objects', 'source');
    }
}
