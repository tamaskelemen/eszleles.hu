<?php

namespace app\components\feed;

/**
 * One batch of a FeedSource.
 */
class FeedPage
{
    /** @var array */
    public $items;

    /** @var string|null the cursor of the next batch, null if there is nothing more */
    public $next;

    public function __construct(array $items, $next)
    {
        $this->items = $items;
        $this->next = $next;
    }
}
