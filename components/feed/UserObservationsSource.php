<?php

namespace app\components\feed;

use app\models\Observe;
use yii\db\Expression;
use yii\web\View;

/**
 * The observations of a user, latest upload first, for the profile.
 *
 * The cursor is the keyset of the last shown one ("<uploaded_at>|<id>"), so new uploads do not shift the batches.
 */
class UserObservationsSource implements FeedSource
{
    /** Observations without an upload time sort last */
    const UPLOADED = "COALESCE(uploaded_at, '-infinity'::timestamp)";

    /** @var int */
    private $userId;

    /**
     * @param int $userId
     */
    public function __construct($userId)
    {
        $this->userId = (int)$userId;
    }

    /**
     * @inheritdoc
     */
    public function fetch($cursor, $limit)
    {
        $query = Observe::find()
            ->ofUser($this->userId)
            ->with('observer', 'comments', 'thumbnail')
            ->orderBy(new Expression(self::UPLOADED . ' DESC, id DESC'));

        if ($cursor !== null) {
            list($uploadedAt, $id) = self::parseCursor($cursor);
            $query->andWhere(new Expression(
                '(' . self::UPLOADED . ', id) < (CAST(:uploadedAt AS timestamp), :id)',
                [':uploadedAt' => $uploadedAt, ':id' => $id]
            ));
        }

        if ($limit !== null) {
            // one more tells whether there is a next batch
            $query->limit($limit + 1);
        }

        $items = $query->all();
        $next = null;

        if ($limit !== null && count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
            /** @var Observe $last */
            $last = end($items);
            $next = ($last->uploaded_at ?: '-infinity') . '|' . $last->id;
        }

        return new FeedPage($items, $next);
    }

    /**
     * @inheritdoc
     */
    public function renderItem(View $view, $item)
    {
        return $view->render('//_common-items/_listItem', ['model' => $item]);
    }

    /**
     * @param string $cursor
     * @return array [uploaded_at, id]
     * @throws InvalidCursorException
     */
    private static function parseCursor($cursor)
    {
        if (!preg_match('/^(-infinity|\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\|(\d+)$/', $cursor, $matches)) {
            throw new InvalidCursorException();
        }

        return [$matches[1], (int)$matches[2]];
    }
}
