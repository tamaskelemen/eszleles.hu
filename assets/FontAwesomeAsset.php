<?php

namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle {

//set the source path using @vendor or another alias like @bower here
  public $sourcePath = '@vendor/fortawesome/font-awesome';

//include css and js relative to the source path set above
  public $css = [
    'css/font-awesome.min.css',
  ];
}