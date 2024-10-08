<?php

use common\models\ComplexPlanChange;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $form \yii\widgets\ActiveForm
 * @var $model \yii\base\Model
 * @var string $attributeName
 * @var array $fieldOptions
 * @var array $options
 * @var string $url
 * @var string $label
 * @var int $index
 * @var string $endpoint
 * @var array $mockFiles
 * @var string $file_type
 * @var array $dropzoneSettings
 */

?>

<div id="upload-multiple" class="dropzone-upload">

    <div data-text-block="" style="margin-top: 30px; display: <?= $mockFiles ? 'none' : 'block' ?>">
        <div class="row text-center">
            <?= \common\helpers\Html::img('@web/img/add_icon.svg')?>
        </div>
        <div class="row text-center">
            <div class="col-xs-12 dropzone-text-block">
                <strong>Перетягніть Ваші файли у виділену область або</strong>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-xs-12">
                <span id="add-multiplefile-btn" class="btn btn-mini">Додайте файл</span><br>
            </div>
        </div>
    </div>

</div>

<?= $form->field($model, 'endpoint')->hiddenInput(['value' => $endpoint])->label(false) ?>
<?= $form->field($model, 'multiple_file_type')->hiddenInput(['value' => $file_type])->label(false) ?>
<?= $form->field($model, 'deleted_multiple_file_ids')->hiddenInput(['data-deleted-multiple-files-ids' => ''])->label(false) ?>

<?php

$uploadUrl = Url::to(['file/save-temp', 'endpoint' => $endpoint]);
$deleteUrl = Url::to(['file/delete-temp', 'endpoint' => $endpoint]);
$clearTempUrl = Url::to(['file/clear-temp', 'endpoint' => $endpoint]);

$mockFiles = json_encode($mockFiles);
$dropzoneSettings = json_encode($dropzoneSettings);

$js = <<<JS

MULTIPLE_FILE.process("$uploadUrl", "$deleteUrl", "$clearTempUrl", $mockFiles, $dropzoneSettings);

JS;

$this->registerJs($js, $this::POS_READY);


?>
