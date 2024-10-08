<?php

namespace common\widgets\switchbtn\assets;

use yii\web\AssetBundle;

/**
 * Class SwitchAsset
 * @package common\widgets\switchbtn\assets
 */
class SwitchAsset extends AssetBundle
{
    public $sourcePath = '@common/widgets/switchbtn/assets/web';

    public $css = [
        'css/switch.css'
    ];

    public $js = [
        'js/switch.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];
}