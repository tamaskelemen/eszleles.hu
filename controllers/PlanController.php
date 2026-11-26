<?php

namespace app\controllers;

use app\components\Flash;
use app\components\ObservingPlans;
use app\models\ObservingPlan;
use app\models\forms\ObservingPlanForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Observing plans made by the users. A private plan can be seen by its owner only, and only the owner can change it.
 */
class PlanController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Objects of a plan. Everybody sees only their own progress.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $plan = ObservingPlan::find()->ofId($id)->with('user')->one();

        if ($plan === null || !$plan->isVisible()) {
            throw new NotFoundHttpException('A keresett oldal nem található');
        }

        $user = Yii::$app->user;

        if ($user->isGuest) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->url);
        }

        $items = ObservingPlans::findItems($plan->id);
        $observations = $user->isGuest ? [] : ObservingPlans::findObservations($plan->id, $user->id);
        $filter = $user->isGuest ? null : Yii::$app->request->get('szures');
        $total = count($items);
        $done = count(array_intersect_key($items, $observations));
        // the numbers stay the same when the list is filtered
        $positions = array_flip(array_keys($items));

        if ($filter === ProgramController::FILTER_DONE) {
            $items = array_intersect_key($items, $observations);
        } else if ($filter === ProgramController::FILTER_MISSING) {
            $items = array_diff_key($items, $observations);
        }

        return $this->render('view', [
            'plan' => $plan,
            'items' => $items,
            'positions' => $positions,
            'observations' => $observations,
            'total' => $total,
            'done' => $done,
            'filter' => $filter,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Throwable
     */
    public function actionCreate()
    {
        $model = new ObservingPlanForm(new ObservingPlan());

        if ($model->load(Yii::$app->request->post()) && $model->save(Yii::$app->user->id)) {
            Flash::addSuccess('Az észlelési tervet létrehoztuk.');

            return $this->redirect(['view', 'id' => $model->getPlan()->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionUpdate($id)
    {
        $model = new ObservingPlanForm($this->findOwnModel($id));

        if ($model->load(Yii::$app->request->post()) && $model->save(Yii::$app->user->id)) {
            Flash::addSuccess('Az észlelési tervet elmentettük.');

            return $this->redirect(['view', 'id' => $model->getPlan()->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $this->findOwnModel($id)->delete();
        Flash::addSuccess('Az észlelési tervet töröltük.');

        return $this->redirect(['/program/index']);
    }

    /**
     * @param int $id
     * @return ObservingPlan
     * @throws NotFoundHttpException
     */
    private function findOwnModel($id)
    {
        $plan = ObservingPlan::find()->ofId($id)->ofUser(Yii::$app->user->id)->one();

        if ($plan === null) {
            throw new NotFoundHttpException('A keresett oldal nem található');
        }

        return $plan;
    }
}
