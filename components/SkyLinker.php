<?php
namespace app\components;

use app\models\ImageTag;
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
            $isFromTitle = $link->source === ObservationSkyObject::SOURCE_TITLE;

            if ($isFromTitle && !isset($objects[$id]) && self::isMadeByMatcher($link)) {
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

        // what is not in the title any more may be in the tags
        $this->syncTags($observe);
    }

    /**
     * Finds the objects by the tags of the images again. What the title or a person has linked is left alone, even if it
     * is rejected.
     *
     * @param Observe $observe
     */
    public function syncTags(Observe $observe)
    {
        $tags = ImageTag::find()->joinWith('image', false)->where(['images.observe_id' => $observe->id])->all();
        $links = $this->findLinks($observe);

        if (empty($tags) && empty($links)) {
            return;
        }

        $known = [];

        foreach ($links as $link) {
            if ($link->isVisible() && $link->source === ObservationSkyObject::SOURCE_TITLE) {
                $known[] = $link->skyObject->attributes;
            }
        }

        $values = array_merge([], ...array_map(function (ImageTag $tag) {
            return $tag->getValues();
        }, $tags));
        $matches = $this->matcher->matchTags($values, $known);

        foreach ($links as $id => $link) {
            $isFromTag = $link->source === ObservationSkyObject::SOURCE_TAG;

            if ($isFromTag && !isset($matches[$id]) && self::isMadeByMatcher($link)) {
                $link->delete();
            }
        }

        foreach ($matches as $id => $match) {
            $link = $links[$id] ?? null;

            if ($link !== null && ($link->source !== ObservationSkyObject::SOURCE_TAG || !self::isMadeByMatcher($link))) {
                continue;
            }

            $this->saveLink(
                $observe, $link, $id, $match['object']['designation'],
                $match['status'], $match['reasons'], ObservationSkyObject::SOURCE_TAG
            );
            $observe->setHasNoSkyObject(false);
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

    private function saveLink(Observe $observe, $link, $skyObjectId, $designation, $status, array $reasons = [], $source = ObservationSkyObject::SOURCE_TITLE)
    {
        $link = $link ?? new ObservationSkyObject();

        $link->observation_id = $observe->id;
        $link->sky_object_id = $skyObjectId;
        $link->designation = $designation;
        $link->status = $status;
        $link->source = $source;
        $link->reason = empty($reasons) ? null : implode(', ', $reasons);

        $link->populateRelation('observation', $observe);
        $link->save(false);
    }

    private static function isMadeByMatcher(ObservationSkyObject $link)
    {
        return in_array($link->status, [ObservationSkyObject::STATUS_AUTO, ObservationSkyObject::STATUS_REVIEW]);
    }
}
