<?php

use yii\db\Migration;

/**
 * Class m210413_162215_add_json_column_to_image_tag
 */
class m210413_162215_add_json_column_to_image_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('image_tags', 'annotation', $this->text());
        $this->addColumn('image_tags', 'annotation_id', $this->text());
        $this->alterColumn('users', 'introduction', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('users', 'introduction', $this->string());
        $this->dropColumn('image_tags', 'annotation');
        $this->dropColumn('image_tags', 'annotation_id');
    }

}
