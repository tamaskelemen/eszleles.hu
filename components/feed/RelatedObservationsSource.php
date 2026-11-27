<?php

namespace app\components\feed;

use app\components\related\RelatedObservations;
use app\models\Observe;
use yii\web\View;

/**
 * The related observations under an observation ("Ez is érdekelhet"), see RelatedObservations.
 *
 * The list is picked at once and cached, so the cursor is just the offset in it.
 */
class RelatedObservationsSource implements FeedSource
{
    /** @var Observe */
    private $observation;

    public function __construct(Observe $observation)
    {
        $this->observation = $observation;
    }

    /**
     * @inheritdoc
     */
    public function fetch($cursor, $limit)
    {
        if ($cursor !== null && !ctype_digit((string)$cursor)) {
            throw new InvalidCursorException();
        }

        $offset = (int)$cursor;
        $total = RelatedObservations::count($this->observation);
        $limit = $limit === null ? $total : $limit;
        $items = RelatedObservations::find($this->observation, $offset, $limit);
        $next = $offset + $limit < $total ? (string)($offset + $limit) : null;

        return new FeedPage($items, $next);
    }

    /**
     * @inheritdoc
     */
    public function renderItem(View $view, $item)
    {
        return $view->render('//_common-items/_listItem', [
            'model' => $item['observation'],
            'reasons' => $item['reasons'],
        ]);
    }
}
