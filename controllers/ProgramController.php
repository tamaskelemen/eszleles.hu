<?php

namespace app\controllers;

use app\components\Badges;
use app\components\SkyPrograms;
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

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'programs' => SkyPrograms::getAll(),
            'totals' => SkyPrograms::getTotals(),
            'progress' => $this->findProgress(),
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
     * @return array badge categories of the user indexed by program
     */
    private function findProgress()
    {
        return Yii::$app->user->isGuest ? [] : array_column(Badges::findAll(Yii::$app->user->id), null, 'key');
    }
}
