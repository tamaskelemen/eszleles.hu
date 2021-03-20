<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%images}}`.
 */
class m210319_220021_create_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('images', [
            'id' => $this->primaryKey(),
            'path' => $this->string()->notNull(),
            'observe_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression("now()"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('images');
    }
}
