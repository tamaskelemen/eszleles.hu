<?php

namespace app\controllers;

use app\components\Badges;
use app\components\Features;
use app\components\ObservingPlans;
use app\components\SkyPrograms;
use app\models\ObservingPlan;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * The observing programs.
 */
class ProgramController extends Controller
{
    const PAGE_SIZE = 500;

    const FILTER_DONE = 'meglevo';
    const FILTER_MISSING = 'hianyzo';

    /** Number of public plans of other users on the index */
    const PUBLIC_PLAN_LIMIT = 30;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->rememberForLogin();

        $user = Yii::$app->user;
        $showsPlans = Features::isEnabled(Features::OBSERVING_PLANS);
        $ownPlans = $user->isGuest || !$showsPlans ? [] : ObservingPlan::find()->ofUser($user->id)->orderBy(['updated_at' => SORT_DESC])->all();
        $publicPlans = !$showsPlans ? [] : ObservingPlan::find()
            ->isPublic()
            ->andFilterWhere(['<>', 'observing_plans.user_id', $user->id])
            ->with('user')
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(self::PUBLIC_PLAN_LIMIT)
            ->all();
        $planIds = array_merge(array_column($ownPlans, 'id'), array_column($publicPlans, 'id'));

        return $this->render('index', [
            'programs' => SkyPrograms::getAll(),
            'totals' => SkyPrograms::getTotals(),
            'progress' => $this->findProgress(),
            'showsPlans' => $showsPlans,
            'ownPlans' => $ownPlans,
            'publicPlans' => $publicPlans,
            'planTotals' => ObservingPlans::countItems($planIds),
            'planDone' => $user->isGuest ? [] : ObservingPlans::countDone($planIds, $user->id),
        ]);
    }

    /**
     * Objects of a program. Everybody sees only their own progress.
     * @param string $key
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(string $key)
    {
        $program = SkyPrograms::getAll()[$key] ?? null;
        $totals = SkyPrograms::getTotals()[$key] ?? null;

        if ($program === null || $totals === null) {
            throw new NotFoundHttpException("A keresett oldal nem található");
        }

        $this->rememberForLogin();

        $user = Yii::$app->user;
        $items = SkyPrograms::findItems($key);
        $observations = $user->isGuest ? [] : SkyPrograms::findObservations($key, $user->id);
        $filter = $user->isGuest ? null : Yii::$app->request->get('szures');

        if ($filter === self::FILTER_DONE) {
            $items = array_intersect_key($items, $observations);
        } else if ($filter === self::FILTER_MISSING) {
            $items = array_diff_key($items, $observations);
        }

        $pagination = new Pagination(['totalCount' => count($items), 'defaultPageSize' => self::PAGE_SIZE, 'pageSizeLimit' => false]);

        return $this->render('view', [
            'key' => $key,
            'program' => $program,
            'totals' => $totals,
            'progress' => $this->findProgress()[$key] ?? null,
            'items' => array_slice($items, $pagination->offset, $pagination->limit, true),
            'observations' => $observations,
            'filter' => $filter,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Guests get back to the program after the login of the call to action.
     */
    private function rememberForLogin()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->url);
        }
    }

    /**
     * @return array badge categories of the user indexed by program
     */
    private function findProgress()
    {
        return Yii::$app->user->isGuest ? [] : array_column(Badges::findAll(Yii::$app->user->id), null, 'key');
    }
}
