<?php

namespace app\components\related\reasons;

use app\components\related\Reason;
use app\components\related\Text;
use app\models\ObservationSkyObject;
use app\models\Observe;
use yii\db\Query;

/**
 * The same identified sky object, or the same title. An eclipse is the same if it was seen on about the same day.
 *
 * Every observation of the Moon and the Sun is of the same object, it would say nothing; meteors have their shower.
 */
class SameObjectReason extends Reason
{
    /** @var int[] ids of the observations sharing a sky object with the shown one, as keys */
    private $linked = [];

    public function getLabel()
    {
        return 'Ugyanaz az objektum';
    }

    public function getWeight()
    {
        return 4;
    }

    public function isSameObject()
    {
        return true;
    }

    public function getColumns()
    {
        return ['object_name', 'date'];
    }

    public function appliesTo(Observe $own)
    {
        return !in_array($own->type, [Observe::TYPE_MOON, Observe::TYPE_SUN, Observe::TYPE_METEOR], true);
    }

    public function prepare(Observe $own)
    {
        $table = ObservationSkyObject::tableName();
        $ids = (new Query())
            ->select('b.observation_id')
            ->distinct()
            ->from(['a' => $table])
            ->innerJoin(['b' => $table], 'b.sky_object_id = a.sky_object_id')
            ->where(['a.observation_id' => $own->id])
            ->andWhere(['<>', 'a.status', ObservationSkyObject::STATUS_REJECTED])
            ->andWhere(['<>', 'b.status', ObservationSkyObject::STATUS_REJECTED])
            ->column();
        $this->linked = array_flip($ids);
    }

    public function matches(Observe $own, array $candidate)
    {
        if ($own->type === Observe::TYPE_ECLIPSE) {
            return $own->date && $candidate['date']
                && abs(strtotime($own->date) - strtotime($candidate['date'])) <= 86400;
        }

        if (isset($this->linked[$candidate['id']])) {
            return true;
        }

        $name = Text::normalize($own->object_name);
        return $name !== '' && $name === Text::normalize($candidate['object_name']);
    }
}
