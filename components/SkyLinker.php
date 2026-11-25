<?php
namespace app\components;

use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;

/**
 * Stores which sky objects an observation is about. What a person has decided (confirmed or rejected) is never changed
 * by the matcher.
 */
class SkyLinker
{
    /** @var SkyMatcher */
    private $matcher;

    public function __construct()
    {
        $this->matcher = new SkyMatcher([SkyObject::class, 'findByAliases']);
    }

    /**
     * Finds the objects by the title again.
     *
     * @param Observe $observe
     */
    public function rematch(Observe $observe)
    {
        if ($observe->has_no_sky_object) {
            return;
        }

        $match = $this->matcher->match($observe->object_name, $observe->constellation);
        $objects = array_column($match['objects'], null, 'id');
        $links = $this->findLinks($observe);

        foreach ($links as $id => $link) {
            if (!isset($objects[$id]) && self::isMadeByMatcher($link)) {
                $link->delete();
            }
        }

        foreach ($objects as $id => $object) {
            $link = $links[$id] ?? null;

            if ($link !== null && !self::isMadeByMatcher($link)) {
                continue;
            }

            $this->saveLink($observe, $link, $id, $object['designation'], $match['status'], $match['reasons']);
        }
    }

    /**
     * Stores the objects a person has chosen.
     *
     * @param Observe $observe
     * @param string[] $items ids of objects, or "id|designation"
     * @param bool $isComplete whether every other object is rejected
     */
    public function confirm(Observe $observe, array $items, $isComplete = true)
    {
        $chosen = SkyObject::findItems($items);
        $links = $this->findLinks($observe);

        foreach ($chosen as $id => $designation) {
            $this->saveLink($observe, $links[$id] ?? null, $id, $designation, ObservationSkyObject::STATUS_CONFIRMED);
        }

        if (!empty($chosen)) {
            $observe->setHasNoSkyObject(false);
        }

        if (!$isComplete) {
            return;
        }

        $match = $this->matcher->match($observe->object_name, $observe->constellation);
        $matched = array_column($match['objects'], null, 'id');

        foreach (array_diff_key($links + $matched, $chosen) as $id => $object) {
            $designation = $links[$id]->designation ?? $matched[$id]['designation'] ?? null;
            $this->saveLink($observe, $links[$id] ?? null, $id, $designation, ObservationSkyObject::STATUS_REJECTED);
        }
    }

    /**
     * @param Observe $observe
     * @return ObservationSkyObject[] indexed by the id of the sky object
     */
    private function findLinks(Observe $observe)
    {
        return ObservationSkyObject::find()->ofObservation($observe->id)->indexBy('sky_object_id')->all();
    }

    private function saveLink(Observe $observe, $link, $skyObjectId, $designation, $status, array $reasons = [])
    {
        $link = $link ?? new ObservationSkyObject();

        $link->observation_id = $observe->id;
        $link->sky_object_id = $skyObjectId;
        $link->designation = $designation;
        $link->status = $status;
        $link->reason = empty($reasons) ? null : implode(', ', $reasons);

        $link->save(false);
    }

    private static function isMadeByMatcher(ObservationSkyObject $link)
    {
        return in_array($link->status, [ObservationSkyObject::STATUS_AUTO, ObservationSkyObject::STATUS_REVIEW]);
    }
}
