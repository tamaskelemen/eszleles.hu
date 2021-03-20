<?php
namespace app\components;

use Yii;

class Flash
{
    public static function addDanger($msg)
    {
        Yii::$app->session->addFlash('danger', $msg);
    }

    public static function addInfo($msg)
    {
        Yii::$app->session->addFlash('info', $msg);
    }

    public static function addSuccess($msg)
    {
        Yii::$app->session->addFlash('success', $msg);
    }

    public static function addWarning($msg)
    {
        Yii::$app->session->addFlash('warning', $msg);
    }
}
