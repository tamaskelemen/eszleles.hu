<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BugReport */

$this->title = 'Hibajelentő';
$this->params['breadcrumbs'][] = ['label' => 'Bug Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container bug-report-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        Tudjuk, hogy az észlelésfeltöltő messze nem tökéletes. Ha valami funkció hiányzik,
        vagy épp nem úgy működik, ahogy annak kéne, kérlek jelezd felénk az alábbi
        űrlap kitöltésével! Ezzel nagymértékben segítve a mi munkánkat.
    </p>

    <p>
        Köszönjük!
    </p>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
