<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;
use yii\bootstrap5\BootstrapAsset;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = ['css/site.css', 'css/shamsi.css', 'css/staff.css'];
    public $js = ['js/shamsi.js', 'js/patient-search.js', 'js/confirm-dialog.js', 'js/debug-toolbar-toggle.js', 'js/ui-enhancements.js', 'js/clinical.js', 'js/staff.js'];
    public $depends = [YiiAsset::class, BootstrapAsset::class];
}
