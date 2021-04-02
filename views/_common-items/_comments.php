<?php
/** @var $commentData ActiveDataProvider */
/** @var $commentForm \app\models\forms\CommentForm */

use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

?>
<div>
    <h2>Hozzászólások (<?= $commentData->totalCount ?>)</h2>
    <?php
    if (!Yii::$app->user->isGuest) {
        echo $this->render('../_common-items/_commentForm',
                ['commentForm' => $commentForm]
            );
    }


    echo ListView::widget([
        'dataProvider' => $commentData,
        'itemView' => '../_common-items/_comment'
    ])

//    foreach ($comments as $comment) {
//        echo $this->render('../_common-items/_comment', ['comment' => $comment]);
//    }
    ?>
</div>