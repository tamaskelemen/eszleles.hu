<?php

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapAsset extends AssetBundle {

//set the source path using @vendor or another alias like @bower here
  public $sourcePath = '@vendor/twbs/bootstrap/dist';

//include css and js relative to the source path set above
  public $css = [
    'css/bootstrap.min.css',
  ];
  public $js = [
    'js/bootstrap.bundle.min.js',
  ];
}