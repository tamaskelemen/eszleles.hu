<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%img_tag}}`.
 */
class m210408_194744_create_img_tag_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('image_tags', [
            'id' => $this->primaryKey(),
            'image_id' => $this->integer(),
            'name' => $this->string(),
            'coord_x' => $this->string(),
            'coord_y' => $this->string(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);

        $this->addForeignKey('fk_image_tags_id_images_id', 'image_tags', 'image_id', 'images', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('image_tags');
    }
}
