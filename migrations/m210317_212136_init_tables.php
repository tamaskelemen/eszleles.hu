<?php

use yii\db\Migration;

/**
 * Class m181125_200300_init_tables
 */
class m210317_212136_init_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'email' => $this->string(),
            'name' => $this->string(),
            'last_login' => $this->timestamp(),
            'password_hash' => $this->string(),
            'password_reset_token' => $this->string(),
            'auth_key' => $this->string(),
            'status' => $this->integer(),
            'terms' => $this->timestamp(),
            'newsletter' => $this->boolean(),
            'created_at' => $this->timestamp()->defaultExpression("now()"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropTable('users');

    }
}
