<?php

namespace app\components\feed;

use yii\web\View;

/**
 * A list that is loaded in batches, see the InfiniteList widget.
 *
 * The cursor is opaque to everything but the source: it can be a keyset ("older than this"), an offset, etc.
 */
interface FeedSource
{
    /**
     * @param string|null $cursor null for the first batch, otherwise the `next` of the previous one
     * @param int|null $limit null for all the rest
     * @return FeedPage
     * @throws InvalidCursorException
     */
    public function fetch($cursor, $limit);

    /**
     * @param View $view
     * @param mixed $item one of FeedPage::$items
     * @return string HTML of one item
     */
    public function renderItem(View $view, $item);
}
