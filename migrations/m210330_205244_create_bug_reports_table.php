<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bug_reports}}`.
 */
class m210330_205244_create_bug_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bug_reports}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'title' => $this->text(),
            'description' => $this->text(),
            'type' => $this->text(),
            'status' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression("NOW()"),
            'updated_at' => $this->timestamp(),
        ]);

        $this->addForeignKey('fk_bug_reports_user_id_users_id', 'bug_reports', 'user_id', 'users', 'id', 'NO ACTION', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bug_reports}}');
    }
}
