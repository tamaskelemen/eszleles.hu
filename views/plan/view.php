<?php

use app\components\Data;
use app\components\SkyPrograms;
use app\controllers\ProgramController;
use app\models\SkyObject;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $plan app\models\ObservingPlan */
/* @var $items array objects in the order of the plan, indexed by object id */
/* @var $positions int[] position in the plan indexed by object id, from 0 */
/* @var $observations array lists of own observations indexed by object id */
/* @var $total int */
/* @var $done int */
/* @var $filter string|null */

$this->title = $plan->name;
$this->params['breadcrumbs'][] = ['label' => 'Észlelőprogramok', 'url' => ['/program/index']];
$this->params['breadcrumbs'][] = $this->title;

$isGuest = Yii::$app->user->isGuest;
$formatter = Yii::$app->formatter;
$filters = [
    null => 'Összes',
    ProgramController::FILTER_DONE => 'Megvan',
    ProgramController::FILTER_MISSING => 'Hiányzik',
];
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="plan-meta">
        Észlelési terv,
        <?= Html::a(Html::encode($plan->user->name), ['/user/profile', 'id' => $plan->user_id]) ?> összeállítása
        <?php if ($plan->isOwn()) { ?>
            · <?= $plan->is_public ? 'nyilvános' : 'csak te látod' ?>
            · <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Módosítás', ['update', 'id' => $plan->id]) ?>
        <?php } ?>
    </p>

    <?php if ($plan->description) { ?>
        <p class="skymap-lead"><?= nl2br(Html::encode($plan->description)) ?></p>
    <?php } ?>

    <?php if ($isGuest) { ?>
        <?= $this->render('//program/_guestCta', ['hasBadges' => false]) ?>
    <?php } ?>

    <p class="badges__count">
        <?php if ($isGuest) { ?>
            <b><?= $formatter->asInteger($total) ?> objektum</b>
        <?php } else { ?>
            <b><?= $formatter->asInteger($done) ?> / <?= $formatter->asInteger($total) ?> objektum megvan</b>
        <?php } ?>
    </p>

    <?php if (!$isGuest && $total > 0) { ?>
        <ul class="nav nav-pills program-filter">
            <?php foreach ($filters as $value => $label) { ?>
                <li class="nav-item">
                    <?= Html::a($label, ['view', 'id' => $plan->id, 'szures' => $value ?: null], [
                        'class' => 'nav-link' . ((string)$filter === (string)$value ? ' active' : ''),
                    ]) ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <?php if ($total === 0) { ?>
        <p>
            Ebben a tervben még nincs objektum.
            <?php if ($plan->isOwn()) { ?>
                <?= Html::a('Objektumok hozzáadása', ['update', 'id' => $plan->id]) ?>
            <?php } ?>
        </p>
    <?php } else if (empty($items)) { ?>
        <p>Nincs ilyen objektum.</p>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table program-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Objektum</th>
                        <th scope="col">Típus</th>
                        <th scope="col">Csillagkép</th>
                        <th scope="col">Deklináció</th>
                        <?php if (!$isGuest) { ?>
                            <th scope="col">Észlelésed</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $id => $object) {
                        $own = $observations[$id] ?? [];
                        $label = Html::encode(SkyObject::buildLabel($object));
                        ?>
                        <tr class="<?= $own ? 'is-done' : '' ?> <?= $object['dec'] >= SkyPrograms::VISIBLE_DEC ? '' : 'is-hidden' ?>">
                            <th scope="row"><?= $positions[$id] + 1 ?></th>
                            <td>
                                <?= $object['is_observed']
                                    ? Html::a($label, ['/sky-map/index', 'objektum' => $id], ['title' => 'Megnyitás az égtérképen'])
                                    : $label ?>
                            </td>
                            <td><?= SkyObject::getTypeName($object['type']) ?></td>
                            <td><?= Html::encode(Data::listConstellation($object['constellation'])) ?></td>
                            <td><?= str_replace(['-', '.'], ['−', ','], sprintf('%.1f', $object['dec'])) ?>°</td>
                            <?php if (!$isGuest) { ?>
                                <td>
                                    <?php if ($own) { ?>
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        <?= Html::a(Html::encode(substr((string)$own[0]['date'], 0, 10) ?: 'megvan'), Url::to(["/{$own[0]['type']}/view", 'id' => $own[0]['id']])) ?>
                                        <?= count($own) > 1 ? '(+' . (count($own) - 1) . ')' : '' ?>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
