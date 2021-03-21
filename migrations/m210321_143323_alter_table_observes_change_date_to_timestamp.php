<?php

use yii\db\Migration;

/**
 * Class m210321_143323_alter_table_observes_change_date_to_timestamp
 */
class m210321_143323_alter_table_observes_change_date_to_timestamp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('observes', 'edited_at', $this->timestamp());
        $this->alterColumn('observes', 'uploaded_at', $this->timestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('observes', 'edited_at', $this->date());
        $this->alterColumn('observes', 'uploaded_at', $this->date());
    }


}
