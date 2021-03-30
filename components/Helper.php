<?php
namespace app\components;

use yii\helpers\Html;

class Helper
{

    public function generateSocialAccountLink($type, $url)
    {
        if (empty($type) || empty($url)) {
            return;
        }

        return Html::a("text", $url);
    }
}