<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css;
    public $js;
    public $depends = [
        'yii\web\YiiAsset',
    ];

    public function __construct($config = [])
    {
        $this->css = [
            'css/bootstrap.css',
            'css/font-awesome4/css/font-awesome.min.css',
            "css/site.css?v=" .filemtime("../web/css/site.css"),
        ];

        $this->js = [
            'js/bootstrap.js',
            'js/bootstrap.bundle.js',
            'js/common.js?v=' . filemtime('../web/js/common.js'),
        ];

        parent::__construct($config);
    }
}
