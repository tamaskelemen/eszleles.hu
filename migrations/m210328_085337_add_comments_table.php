<?php

use yii\db\Migration;

/**
 * Class m210328_085337_add_comments_table
 */
class m210328_085337_add_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('comments', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'observation_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression("now()"),
            'comment' => $this->text()
        ]);

        $this->addForeignKey('fk_comments_user_id_user_id', 'comments', 'user_id', 'users', 'id', 'NO ACTION', 'CASCADE');
        $this->addForeignKey('fk_comments_observation_id_observations_id', 'comments', 'observation_id', 'observations', 'id', 'NO ACTION', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropTable('comments');
    }
}
