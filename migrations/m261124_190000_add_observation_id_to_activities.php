<?php

use yii\db\Migration;

/**
 * Adds the column `observation_id` to table `{{%activities}}`.
 *
 * It is the observation the activity is about, if there is any. There is no foreign key on purpose,
 * the log has to keep the id even if the observation is deleted.
 */
class m261124_190000_add_observation_id_to_activities extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('activities', 'observation_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('activities', 'observation_id');
    }
}
