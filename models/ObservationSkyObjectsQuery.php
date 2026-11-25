<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ObservationSkyObject]].
 *
 * @see ObservationSkyObject
 */
class ObservationSkyObjectsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return ObservationSkyObject[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ObservationSkyObject|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id integer
     * @return ObservationSkyObjectsQuery
     */
    public function ofObservation($id)
    {
        return $this->andWhere(['observation_sky_objects.observation_id' => $id]);
    }

    /**
     * @param $status string|array
     * @return ObservationSkyObjectsQuery
     */
    public function ofStatus($status)
    {
        return $this->andWhere(['observation_sky_objects.status' => $status]);
    }
}
