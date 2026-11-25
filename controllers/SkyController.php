<?php

namespace app\controllers;

use app\components\SkyMatcher;
use app\models\SkyObject;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\Response;

/**
 * Serves the sky objects for the sky object input.
 */
class SkyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'json' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Objects found in the title of an observation.
     * @param string $title
     * @return array
     */
    public function actionMatch($title)
    {
        $matcher = new SkyMatcher([SkyObject::class, 'findByAliases']);

        return array_map([SkyObject::class, 'buildItem'], $matcher->match(mb_substr($title, 0, 255))['objects']);
    }

    /**
     * @param string $q
     * @return array
     */
    public function actionSearch($q)
    {
        return array_map([SkyObject::class, 'buildItem'], SkyObject::search(mb_substr($q, 0, 255)));
    }
}
