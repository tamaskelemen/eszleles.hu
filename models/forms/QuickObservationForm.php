<?php

namespace app\models\forms;

use app\models\Activity;
use app\models\Observe;
use app\models\SkyObject;
use Yii;
use yii\base\Model;

/**
 * A deep sky observation written down under a recommendation of the home page. Only the description is written by
 * the user, the rest is filled in (see getDefaults()) and can be changed. The observation is linked to the object, so it
 * counts as observed right away.
 */
class QuickObservationForm extends Model
{
    /** Before this hour the observation belongs to the night that started on the previous day */
    const NIGHT_END_HOUR = 12;

    /** @var int */
    public $skyObjectId;
    /** @var string */
    public $description;
    /** @var string */
    public $telescope;
    /** @var string */
    public $location;
    /** @var string yyyy-mm-dd */
    public $date;

    /** @var int|null of the saved observation */
    public $id;

    /** @var SkyObject|null */
    private $skyObject;

    public function rules()
    {
        return [
            [['description', 'telescope', 'location'], 'trim'],
            [['skyObjectId'], 'required'],
            [['description'], 'required', 'message' => 'Írd le, mit láttál!'],
            [['telescope'], 'required', 'message' => 'Add meg, milyen távcsővel észleltél!'],
            [['location'], 'required', 'message' => 'Add meg az észlelés helyszínét!'],
            [['date'], 'required', 'message' => 'Add meg az észlelés napját!'],
            [['skyObjectId'], 'integer'],
            [['skyObjectId'], 'validateSkyObject'],
            [['description'], 'string'],
            [['telescope', 'location'], 'string', 'max' => 255],
            [['date'], 'date', 'format' => 'php:Y-m-d', 'max' => date('Y-m-d'), 'tooBig' => 'Az észlelés napja nem lehet a jövőben.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'description' => 'Leírás',
            'telescope' => 'Optika',
            'location' => 'Helyszín',
            'date' => 'Időpont',
        ];
    }

    public function validateSkyObject($attribute)
    {
        $this->skyObject = SkyObject::findOne((int)$this->$attribute);

        if ($this->skyObject === null) {
            $this->addError($attribute, 'Ismeretlen objektum.');
        }
    }

    /**
     * @param int $userId
     * @param string|null $placeName the place of the recommender, if the user has never given a location
     * @return array telescope, location and date to fill in
     */
    public static function getDefaults($userId, $placeName = null)
    {
        $previous = Observe::getPreviousValues($userId);

        return [
            'telescope' => $previous['telescope'][0] ?? '',
            'location' => $previous['location'][0] ?? (string)$placeName,
            // observing goes on after midnight
            'date' => date('Y-m-d', (int)date('G') < self::NIGHT_END_HOUR ? strtotime('-1 day') : time()),
            'previous' => $previous,
        ];
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function save($userId)
    {
        if (!$this->validate()) {
            return false;
        }

        $observe = new Observe();
        $observe->observer_id = $userId;
        $observe->type = Observe::TYPE_DEEP_SKY;
        $observe->object_name = SkyObject::buildLabel($this->skyObject->attributes);
        $observe->telescope = $this->telescope;
        $observe->location = $this->location;
        $observe->date = $this->date;
        $observe->description = $this->description;
        // linked to the recommended object, the title is not matched again
        $observe->skyObjectItems = [(string)$this->skyObject->id];
        $observe->skyObjectTitle = $observe->object_name;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$observe->save()) {
                $transaction->rollBack();
                $this->addErrors($observe->getErrors());
                return false;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            $this->addError('description', 'Az észlelést nem sikerült elmenteni, próbáld újra!');
            return false;
        }

        $this->id = $observe->id;
        Activity::log($userId, Activity::TYPE_OBSERVATION_CREATE, $observe->id);

        return true;
    }

    /**
     * @return Observe|null
     */
    public function getObservation()
    {
        return $this->id === null ? null : Observe::findOne($this->id);
    }
}
