<?php

namespace common\widgets\multiple_file\assets;

use common\assets\DropzoneSourceAsset;
use yii\web\AssetBundle;

/**
 * Class MultipleFileAsset
 * @package common\widgets\switchbtn\assets
 */
class MultipleFileAsset extends AssetBundle
{
    public $sourcePath = '@common/widgets/multiple_file/assets/web';

    public $css = [
    ];

    public $js = [
        'js/multiple_file.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        DropzoneSourceAsset::class
    ];
}