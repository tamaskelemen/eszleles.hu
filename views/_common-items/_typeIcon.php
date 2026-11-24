<?php
/** @var $type string */

use app\models\Observe;

$shapes = [
    Observe::TYPE_DEEP_SKY => '<circle cx="12" cy="12" r="2" fill="currentColor" stroke="none"/><path d="M16.8 12.5c-.6 3-4.2 4.8-7.3 3.2C5 13.4 5.8 6.2 11.5 4c3.2-1.2 6.8-.6 9 1.3"/><path d="M7.2 11.5c.6-3 4.2-4.8 7.3-3.2 4.5 2.3 3.7 9.5-2 11.7-3.2 1.2-6.8.6-9-1.3"/>',
    Observe::TYPE_MOON => '<path d="M19.5 14.6A8 8 0 1 1 9.4 4.5a6.4 6.4 0 0 0 10.1 10.1z"/>',
    Observe::TYPE_PLANET => '<circle cx="12" cy="12" r="4.8"/><path d="M8 14.6c-3.6 1.9-5.8 3.2-5.4 4 .6 1.1 5.5-.8 10.9-3.9 5.4-3.1 9.2-6.3 8.6-7.4-.4-.8-2.7-.1-6 1.5"/>',
    Observe::TYPE_METEOR => '<circle cx="6.5" cy="17.5" r="2.5" fill="currentColor" stroke="none"/><path d="M9.5 14.5L20 4M7.5 12.5L13 7M11.5 16.5L17 11"/>',
    Observe::TYPE_LANDSCAPE => '<path d="M2.5 19l6-9 4 5.5 2.5-3 6.5 6.5z"/><path d="M17 4v4M15 6h4"/>',
    Observe::TYPE_COMET => '<circle cx="7.5" cy="16.5" r="3"/><path d="M8.5 11.5C11 8 14.5 5 20 3.5M12.5 15.5c3.5-2.5 6-6.5 7.5-12M10.5 13.5L20 3.5"/>',
    Observe::TYPE_SUN => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.3 5.3l2.1 2.1M16.6 16.6l2.1 2.1M5.3 18.7l2.1-2.1M16.6 7.4l2.1-2.1"/>',
    Observe::TYPE_ECLIPSE => '<circle cx="12" cy="12" r="8"/><circle cx="14" cy="10.8" r="6" fill="currentColor" stroke="none"/>',
];
?>
<svg class="type-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $shapes[$type] ?? '' ?></svg>
