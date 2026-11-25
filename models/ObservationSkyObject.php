<?php

namespace app\models;

use app\components\SkyMap;
use app\components\SkyMatcher;

/**
 * This is the model class for table "observation_sky_objects".
 *
 * Tells that an observation is about a sky object.
 *
 * @property int $id
 * @property int $observation_id
 * @property int $sky_object_id
 * @property string|null $designation the catalog number used in the title, if the object is known by another one
 * @property string $status
 * @property string|null $reason why it has to be reviewed
 * @property string $created_at
 *
 * @property Observe $observation
 * @property SkyObject $skyObject
 */
class ObservationSkyObject extends \yii\db\ActiveRecord
{
    /** found by the matcher */
    const STATUS_AUTO = SkyMatcher::STATUS_AUTO;
    /** found by the matcher, but an admin has to approve it */
    const STATUS_REVIEW = SkyMatcher::STATUS_REVIEW;
    /** approved by the uploader or an admin */
    const STATUS_CONFIRMED = 'confirmed';
    /** removed by the uploader or an admin */
    const STATUS_REJECTED = 'rejected';

    const VISIBLE_STATUSES = [self::STATUS_AUTO, self::STATUS_CONFIRMED];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'observation_sky_objects';
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        SkyMap::clearCache();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
        SkyMap::clearCache();
    }

    /**
     * @return array
     */
    public static function getAllStatuses()
    {
        return [
            self::STATUS_AUTO => 'automatikus',
            self::STATUS_REVIEW => 'jóváhagyásra vár',
            self::STATUS_CONFIRMED => 'jóváhagyva',
            self::STATUS_REJECTED => 'elutasítva',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObservation()
    {
        return $this->hasOne(Observe::class, ['id' => 'observation_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSkyObject()
    {
        return $this->hasOne(SkyObject::class, ['id' => 'sky_object_id']);
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return in_array($this->status, self::VISIBLE_STATUSES);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return SkyObject::buildLabel(['designation' => $this->designation] + $this->skyObject->attributes);
    }

    /**
     * {@inheritdoc}
     * @return ObservationSkyObjectsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ObservationSkyObjectsQuery(get_called_class());
    }
}
