<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%observes}}`.
 */
class m210319_204319_create_observes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('observations', [
            'id' => $this->primaryKey(),
            'object_name' => $this->string(),
            'catalog_number' => $this->string(),
            'constellation' => $this->string(),
            'object_type' => $this->string(),
            'telescope' => $this->string(),
            'camera' => $this->string(),
            'seeing' => $this->tinyInteger(),
            'transparency' => $this->tinyInteger(),
            'location' => $this->string(),
            'source' => $this->string(),
            'date' => $this->date(),
            'observer_id' => $this->integer(),
            'description' => $this->text(),
            'uploaded_at' => $this->date()->defaultExpression("now()"),
            'edited_at' => $this->date(),
        ]);

        $this->addForeignKey('fk_observes_observer_id_user_id', 'observations', 'observer_id', 'users', 'id', 'NO ACTION', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('observations');
    }
}
