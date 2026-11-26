<?php

namespace app\controllers;

use app\components\Flash;
use app\components\ObservingPlans;
use app\models\Observe;
use app\models\ObservingPlan;
use app\models\ObservingPlanItem;
use app\models\ObservingPlanItemObservation;
use app\models\SkyObject;
use app\models\forms\ObservingPlanForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Observing plans made by the users. A private plan can be seen by its owner only, and only the owner can change it.
 */
class PlanController extends Controller
{
    /** Number of own observations offered for attaching */
    const OBSERVATION_LIMIT = 20;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'update', 'delete', 'attach', 'detach', 'observations', 'object-plans', 'toggle-object'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                // the login brings the guest back here
                'denyCallback' => function () {
                    if (Yii::$app->user->isGuest && $this->action->id === 'create') {
                        Flash::addInfo('Az észlelési terv összeállításához lépj be, utána folytathatod.');
                    }

                    Yii::$app->user->loginRequired();
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'attach' => ['POST'],
                    'detach' => ['POST'],
                    'toggle-object' => ['POST'],
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
        $objectId = Yii::$app->request->get('objektum');

        // saved from the sky map
        if ($objectId !== null && !Yii::$app->request->isPost && SkyObject::find()->where(['id' => (int)$objectId])->exists()) {
            $model->prefill([(int)$objectId => null]);
        }

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
     * Attaches an own observation to an object of a visible plan by hand.
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionAttach()
    {
        $request = Yii::$app->request;
        $item = $this->findVisibleItem((int)$request->post('item_id'));
        $observation = Observe::find()->ofId((int)$request->post('observation_id'))->ofUser(Yii::$app->user->id)->one();

        if ($observation === null) {
            Flash::addWarning('Csak a saját észlelésedet választhatod ki.');
        } else if (ObservingPlanItemObservation::find()->where(['item_id' => $item->id, 'observation_id' => $observation->id])->exists()) {
            Flash::addInfo('Ezt az észlelést már kiválasztottad ehhez az objektumhoz.');
        } else {
            (new ObservingPlanItemObservation(['item_id' => $item->id, 'observation_id' => $observation->id]))->save(false);
            Flash::addSuccess('Észleltként megjelöltük.');
        }

        return $this->redirectToPlan($item->plan_id);
    }

    /**
     * Removes an observation attached by hand. Only the observer can do it.
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionDetach()
    {
        $request = Yii::$app->request;
        $item = $this->findVisibleItem((int)$request->post('item_id'));
        $link = ObservingPlanItemObservation::find()
            ->innerJoinWith('observation o', false)
            ->where(['item_id' => $item->id, 'observation_id' => (int)$request->post('observation_id'), 'o.observer_id' => Yii::$app->user->id])
            ->one();

        if ($link !== null) {
            $link->delete();
            Flash::addSuccess('Az észlelést eltávolítottuk az objektum mellől.');
        }

        return $this->redirectToPlan($item->plan_id);
    }

    /**
     * Own observations for the attaching dialog, the latest first.
     * @param string $q part of the title
     * @return array
     */
    public function actionObservations($q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim(mb_substr((string)$q, 0, 100));
        $observations = Observe::find()
            ->select(['id', 'type', 'object_name', 'date'])
            ->ofUser(Yii::$app->user->id)
            ->andFilterWhere(['ilike', 'object_name', $q])
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(self::OBSERVATION_LIMIT)
            ->asArray()
            ->all();

        return array_map(function ($observation) {
            return [
                'id' => (int)$observation['id'],
                'label' => StringHelper::truncate((string)$observation['object_name'], 80),
                'details' => trim($observation['date'] . ', ' . Observe::getTypeName($observation['type']), ', '),
            ];
        }, $observations);
    }

    /**
     * Own plans for the save button of the sky map, the latest first.
     * @param int $id sky object
     * @return array
     */
    public function actionObjectPlans(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->listPlans($id);
    }

    /**
     * Saves an object to an own plan, or removes it from there.
     * @return array the own plans, as in actionObjectPlans()
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionToggleObject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $plan = $this->findOwnModel((int)$request->post('plan_id'));
        $objectId = (int)$request->post('sky_object_id');

        if (!SkyObject::find()->where(['id' => $objectId])->exists()) {
            throw new NotFoundHttpException('A keresett oldal nem található');
        }

        $item = ObservingPlanItem::find()->ofPlan($plan->id)->andWhere(['sky_object_id' => $objectId])->one();
        $message = null;

        if ($item !== null) {
            $item->delete();
            $plan->save(false);
        } else if (ObservingPlanItem::find()->ofPlan($plan->id)->count() >= ObservingPlan::ITEM_LIMIT) {
            $message = 'Legfeljebb ' . ObservingPlan::ITEM_LIMIT . ' objektum lehet egy tervben.';
        } else {
            (new ObservingPlanItem([
                'plan_id' => $plan->id,
                'sky_object_id' => $objectId,
                'position' => (int)ObservingPlanItem::find()->ofPlan($plan->id)->max('position') + 1,
            ]))->save(false);
            $plan->save(false);
        }

        return ['plans' => $this->listPlans($objectId), 'message' => $message];
    }

    /**
     * @param int $objectId
     * @return array own plans (id, name, url, hasObject), the newest first, so that the order does not change while saving
     */
    private function listPlans(int $objectId)
    {
        $plans = ObservingPlan::find()->ofUser(Yii::$app->user->id)->orderBy(['id' => SORT_DESC])->all();
        $saved = ObservingPlanItem::find()
            ->select('plan_id')
            ->where(['plan_id' => array_column($plans, 'id'), 'sky_object_id' => $objectId])
            ->column();

        return array_map(function (ObservingPlan $plan) use ($saved) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'url' => Url::to(['view', 'id' => $plan->id]),
                'hasObject' => in_array($plan->id, $saved),
            ];
        }, $plans);
    }

    /**
     * @param int $id
     * @return ObservingPlanItem of a plan the visitor may see
     * @throws NotFoundHttpException
     */
    private function findVisibleItem(int $id)
    {
        $item = ObservingPlanItem::find()->where(['id' => $id])->with('plan')->one();

        if ($item === null || !$item->plan->isVisible()) {
            throw new NotFoundHttpException('A keresett oldal nem található');
        }

        return $item;
    }

    /**
     * Back to the plan, with the filter it was opened with.
     * @param int $planId
     * @return Response
     */
    private function redirectToPlan(int $planId)
    {
        $filter = Yii::$app->request->post('szures');

        return $this->redirect(['view', 'id' => $planId, 'szures' => $filter ?: null, '#' => 'terv-' . (int)Yii::$app->request->post('item_id')]);
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
