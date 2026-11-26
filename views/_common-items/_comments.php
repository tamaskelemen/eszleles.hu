<?php
/** @var $commentData ActiveDataProvider */
/** @var $commentForm \app\models\forms\CommentForm */

use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

$commentCount = $commentData->totalCount;
foreach ($commentData->getModels() as $comment) {
    $commentCount += count($comment->replies);
}
?>
<h2>Hozzászólások (<?= $commentCount ?>)</h2>
<?php
echo ListView::widget([
    'dataProvider' => $commentData,
    'itemView' => '../_common-items/_commentThread',
    'summary' => '',
    'emptyText' => 'Még nincs hozzászólás'
]);

if (!Yii::$app->user->isGuest) {
    $this->registerJsFile('/js/comments.js?v=' . filemtime(Yii::getAlias('@webroot/js/comments.js')), ['depends' => 'app\assets\AppAsset']);

    echo $this->render('../_common-items/_commentForm',
            ['commentForm' => $commentForm]
        );
}
?>
