<?php

namespace app\components\related;

use app\components\related\reasons\NearbyReason;
use app\components\related\reasons\SameNightReason;
use app\components\related\reasons\SameObjectReason;
use app\components\related\reasons\SameShowerReason;
use app\components\related\reasons\SameWeekReason;
use app\components\related\reasons\SimilarCameraReason;
use app\components\related\reasons\SimilarMechanicsReason;
use app\components\related\reasons\SimilarTelescopeReason;
use app\models\Observe;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Finds the observations shown under an observation ("Ez is érdekelhet"), each with the reasons it is related.
 *
 * The candidates are of the same type, each matching reason adds its weight, the largest sums are shown (ties in a
 * random order). The whole list (TOTAL) is picked at once and cached, it is shown in batches of BATCH as it is scrolled
 * (see RelatedObservationsSource). If none of the first batch is of the same object, its last card is a random
 * observation of any type.
 */
class RelatedObservations
{
    /** The reasons, in the order they are shown on a card */
    const REASONS = [
        SameObjectReason::class,
        SameShowerReason::class,
        NearbyReason::class,
        SameNightReason::class,
        SameWeekReason::class,
        SimilarTelescopeReason::class,
        SimilarCameraReason::class,
        SimilarMechanicsReason::class,
    ];

    const RANDOM_LABEL = 'Random';

    /** Size of a batch, the first one is shown with the page */
    const BATCH = 4;

    /** At most this many are shown: the first batch and 5 more */
    const TOTAL = self::BATCH * 6;

    /** At most this many cards of a batch come from the observer of the shown observation */
    const MAX_FROM_SAME_OBSERVER = 2;

    /** The latest this many observations of the type are compared */
    const MAX_CANDIDATES = 3000;

    const CACHE_DURATION = 3600;

    /**
     * @param Observe $observation
     * @param int $offset
     * @param int $limit
     * @return array[] list of ['observation' => Observe, 'reasons' => string[]]
     */
    public static function find(Observe $observation, $offset = 0, $limit = self::BATCH)
    {
        $picks = array_slice(self::findIds($observation), $offset, $limit, true);

        $models = Observe::find()
            ->with(['observer', 'comments', 'thumbnail'])
            ->where(['id' => array_keys($picks)])
            ->indexBy('id')
            ->all();

        $result = [];
        foreach ($picks as $id => $reasons) {
            if (isset($models[$id])) {
                $result[] = ['observation' => $models[$id], 'reasons' => $reasons];
            }
        }
        return $result;
    }

    /**
     * @param Observe $observation
     * @return int number of related observations, at most TOTAL
     */
    public static function count(Observe $observation)
    {
        return count(self::findIds($observation));
    }

    /**
     * @param Observe $observation
     * @return array labels of the reasons by observation id, in the order of the cards
     */
    private static function findIds(Observe $observation)
    {
        return Yii::$app->cache->getOrSet(['related-observations', $observation->id, self::TOTAL], function () use ($observation) {
            return self::pick($observation, self::TOTAL);
        }, self::CACHE_DURATION);
    }

    /**
     * @param Observe $observation
     * @return Reason[] the ones that can match anything for the observation, prepared
     */
    private static function reasonsFor(Observe $observation)
    {
        $reasons = [];
        foreach (self::REASONS as $class) {
            /** @var Reason $reason */
            $reason = new $class();
            if ($reason->appliesTo($observation)) {
                $reason->prepare($observation);
                $reasons[] = $reason;
            }
        }
        return $reasons;
    }

    /**
     * @param Observe $observation
     * @param int $limit
     * @return array labels of the reasons by observation id, in the order of the cards
     */
    private static function pick(Observe $observation, $limit)
    {
        $reasons = self::reasonsFor($observation);

        $columns = ['id', 'observer_id'];
        foreach ($reasons as $reason) {
            $columns = array_merge($columns, $reason->getColumns());
        }

        $candidates = (new Query())
            ->select(array_unique($columns))
            ->from(Observe::tableName())
            ->where(['type' => $observation->type])
            ->andWhere(['<>', 'id', $observation->id])
            ->orderBy(['uploaded_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(self::MAX_CANDIDATES)
            ->all();

        $ranked = [];
        foreach ($candidates as $candidate) {
            $matching = array_values(array_filter($reasons, function (Reason $reason) use ($observation, $candidate) {
                return $reason->matches($observation, $candidate);
            }));
            $score = array_sum(array_map(function (Reason $reason) {
                return $reason->getWeight();
            }, $matching));
            $ranked[] = [
                'candidate' => $candidate,
                'reasons' => $matching,
                // the random part only orders the ties
                'rank' => $score + mt_rand() / (mt_getrandmax() + 1),
            ];
        }
        usort($ranked, function ($a, $b) {
            return $b['rank'] <=> $a['rank'];
        });

        $picks = [];
        $hasSameObject = false;
        $queue = $ranked;
        while ($queue && count($picks) < $limit) {
            // the ones over the limit of the observer are kept for the next batch
            $deferred = [];
            $fromSameObserver = 0;
            $inBatch = 0;
            while ($queue && $inBatch < self::BATCH && count($picks) < $limit) {
                $item = array_shift($queue);
                $candidate = $item['candidate'];
                if ($observation->observer_id && (int)$candidate['observer_id'] === (int)$observation->observer_id) {
                    if ($fromSameObserver >= self::MAX_FROM_SAME_OBSERVER) {
                        $deferred[] = $item;
                        continue;
                    }
                    $fromSameObserver++;
                }

                $labels = [];
                foreach ($item['reasons'] as $reason) {
                    $labels[] = $reason->getLabel();
                    // only the first batch is shown for sure
                    $hasSameObject = $hasSameObject || ($reason->isSameObject() && count($picks) < self::BATCH);
                }
                if (!$labels) {
                    $labels[] = 'Szintén ' . mb_strtolower(Observe::getTypeName($observation->type)) . ' észlelés';
                }
                $picks[$candidate['id']] = $labels;
                $inBatch++;
            }

            // only the observer's ones are left, they would fill the next batches
            if (!$queue) {
                break;
            }
            $queue = array_merge($deferred, $queue);
        }

        if (!$hasSameObject) {
            $randomId = (new Query())
                ->select('id')
                ->from(Observe::tableName())
                ->where(['not in', 'id', array_merge([$observation->id], array_keys($picks))])
                ->orderBy(new Expression('random()'))
                ->scalar();
            if ($randomId) {
                // the last card of the first batch, the rest move on by one
                $position = min(self::BATCH, count($picks) + 1) - 1;
                $ids = array_keys($picks);
                array_splice($ids, $position, 0, [$randomId]);
                $labels = array_values($picks);
                array_splice($labels, $position, 0, [[self::RANDOM_LABEL]]);
                $picks = array_slice(array_combine($ids, $labels), 0, $limit, true);
            }
        }

        return $picks;
    }
}
