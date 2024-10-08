<?php

use common\models\ComplexPlanChange;
use common\models\file\entities\InspectionReasonCancelledFile;
use common\models\file\entities\InspectionReasonFile;
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
 * @var string $file_type
 * @var string $delete_file_attribute
 * @var string $has_ipn_attribute
 * @var string $file_type_attribute
 */

$displayDownloadBlock = false;
$displayFileBlock = false;

$entityFile = null;
$fileUrl = null;

$id_entity_file = 0;

// secret fields
$showHasIpn = false;
$showHasSecretInfo = false;
$showSecretPart = false;
$showSecretDescription = false;
$displaySecretBlock = false;

$getSecretValue = function($attributeName, $model, $entityFile) {

    // detect secret values
    if(!($value = $model->{$attributeName})) {
        if($entityFile) {
            $value = $entityFile->{$attributeName};
        }
    }

    return $value;
};

if($model instanceof \frontend\models\InspectionReason) {

    if ($file_type == InspectionReasonFile::TYPE) {
        $entityFile = \common\models\file\entities\InspectionReasonFile::findOne(['inspection_reason_id' => $model->id]);
        $showHasIpn = true;
    }

    if ($file_type == InspectionReasonCancelledFile::TYPE) {
        $entityFile = \common\models\file\entities\InspectionReasonCancelledFile::findOne(['inspection_reason_id' => $model->id]);
        $showHasIpn = true;
    }

    if($file_type == \common\models\file\entities\PrivateInspectionReasonCancelledFile::TYPE) {
        $entityFile = \common\models\file\entities\PrivateInspectionReasonCancelledFile::findOne(['inspection_reason_id' => $model->id]);
        $showHasIpn = false;
    }

    if($file_type == \common\models\file\entities\PrivateInspectionReasonFile::TYPE) {
        $entityFile = \common\models\file\entities\PrivateInspectionReasonFile::findOne(['inspection_reason_id' => $model->id]);
        $showHasIpn = false;
    }

} elseif ($model instanceof \common\models\AdditionalAttribute) {
    $id_name = $model->id_name;
    $additionalAttribute = \common\models\AdditionalAttribute::findOne([$id_name => $model->{$id_name}, 'attribute_name' => $model->attribute_name]);
    if($additionalAttribute) {
        $entityFile = \common\models\file\entities\AdditionalAttributeFile::findOne(['additional_attribute_id' => $additionalAttribute->id]);
    }

} elseif ($model instanceof \common\models\AnnualInspection) {

    if ($file_type == \common\models\file\entities\PrivateResultActFile::TYPE) {
        $showHasIpn = false;
        $showHasSecretInfo = false;
        $entityFile = \common\models\file\entities\PrivateResultActFile::findOne(['inspection_id' => $model->id]);
    } else {
        $entityFile = \common\models\file\entities\ResultActFile::findOne(['inspection_id' => $model->id]);

        $showHasIpn = true;
        $showHasSecretInfo = true;

        if($entityFile) {
            if($entityFile->has_secret_info) {
                $displaySecretBlock = true;
            }
        }
    }

} elseif($model instanceof \common\models\InspectionDocument) {

    if ($file_type == \common\models\file\entities\PrivateInspectionDocumentFile::TYPE) {
        $showHasIpn = false;
        $showHasSecretInfo = false;
        $entityFile = \common\models\file\entities\PrivateInspectionDocumentFile::findOne(['inspection_document_id' => $model->id]);
    } else {
        $entityFile = \common\models\file\entities\InspectionDocumentFile::findOne(['inspection_document_id' => $model->id]);

        $showHasIpn = true;
        $showHasSecretInfo = true;

        if($entityFile) {
            if($entityFile->has_secret_info) {
                $displaySecretBlock = true;
            }
        }
    }

} elseif ($model instanceof \common\models\unplanned_reason\meta\UnplannedReason) {

    $className = 'common\models\file\entities\UnplannedReason'.$model->type.'File';
    /* @var $entityFile \common\db\ActiveRecord */
    $entityFile = (new $className)::findOne(['unplanned_reason_id' => $model->id]);

    if(\common\models\file\meta\BaseFile::getPrivateFileSettings(null, $className)) {

        $showHasIpn = true;
        $showHasSecretInfo = true;

        if($entityFile) {
            if($entityFile->has_secret_info) {
                $displaySecretBlock = true;
            }
        }
    }

    $private_setting = \common\models\file\meta\BaseFile::getPrivateFileSettings($file_type);

    if($private_setting) {
        $entityFile = (new $private_setting['private_class_name'])::findOne(['unplanned_reason_id' => $model->id]);
        $showHasIpn = false;
        $showHasSecretInfo = false;
    }

} elseif ($model instanceof \frontend\models\form\ComplexPlanChangeForm) {
    $entityFile = \common\models\file\entities\ComplexPlanChangeFile::findOne(['complex_plan_change_id' => $model->id]);

    $showHasIpn = $showHasSecretInfo = $displaySecretBlock = false;

} elseif ($model instanceof \frontend\models\form\PlanChangeForm) {
    $entityFile = \common\models\file\entities\PlanChangeFile::findOne(['plan_change_id' => $model->id]);

    $showHasIpn = $showHasSecretInfo = $displaySecretBlock = false;

}  elseif ($model instanceof \common\models\ComplexPlan) {

    $entityFile = \common\models\file\entities\ComplexPlanFile::findOne(['complex_plan_id' => $model->id]);

    $showHasIpn = $showHasSecretInfo = $displaySecretBlock = false;

} elseif ($model instanceof \common\models\subject\SubjectAppeal) {

    if($file_type == \common\models\file\entities\SubjectAppealFile::TYPE) {
        $entityFile = \common\models\file\entities\SubjectAppealFile::findOne(['subject_appeal_id' => $model->id]);

    } elseif($file_type == \common\models\file\entities\AnswerAppealFile::TYPE) {
        $entityFile = \common\models\file\entities\AnswerAppealFile::findOne(['subject_appeal_id' => $model->id]);
    }
}

if($entityFile) {
    $displayDownloadBlock = true;

    $fileUrl = $entityFile->getUrl();

    $id_entity_file = $entityFile->id;

    if($model->getErrors($attributeName)) {
        $displayFileBlock = true;
        $displayDownloadBlock = false;
    }

} else {
    $displayFileBlock = true;
}

?>

<div data-file-container class="data-file-container">
    <div data-download-block style="display: <?= $displayDownloadBlock ? 'block' : 'none' ?>;">

        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" data-remove-file="<?=$id_entity_file ?>" class="close" data-toggle="tooltip" data-placement="top" title="Видалити файл"><span aria-hidden="true">&times;</span></button>
            <?= \yii\helpers\Html::a('Скачати "'.$label.'"', $fileUrl, ['class' => 'alert-link', 'target' => '_blank']) ?>
        </div>

    </div>
    <div data-file-block style="display: <?= $displayFileBlock ? 'block' : 'none' ?>;">

        <?= $form->field($model, isset($index) ? "[{$index}]$delete_file_attribute" : $delete_file_attribute)->hiddenInput(['data-is-file-deleted' => true, 'value' => $model->{$delete_file_attribute} ? : 0])->label(false) ?>
        <?= $form->field($model, isset($index) ? "[{$index}]$file_type_attribute" : $file_type_attribute)->hiddenInput(['value' => $file_type])->label(false) ?>
        <?= $form->field($model, isset($index) ? "[{$index}]".$attributeName : $attributeName, $fieldOptions)->fileInput($options)->label($label) ?>

    </div>
</div>

<?php

if($showHasIpn) {

    $model->{$has_ipn_attribute} = $getSecretValue('has_ipn', $model, $entityFile);
    echo $form->field($model, isset($index) ? "[{$index}]$has_ipn_attribute" : $has_ipn_attribute)->checkbox([
            'label' => 'Файл містить незаретушований ІПН фізичної особи',
        'data-has-ipn-checkbox' => '',
    ]);
}

if($showHasSecretInfo) {

    if($model->has_secret_info) {
        $displaySecretBlock = true;
    }

    $model->has_secret_info = $getSecretValue('has_secret_info', $model, $entityFile);
    echo $form->field($model, isset($index) ? "[{$index}]has_secret_info" : 'has_secret_info')->checkbox([
        'data-has-secret-info-checkbox' => '',
        'label' => 'Файл містить інформацію, що не підлягає публікації'
    ]);

    if(false) {
        echo '<div data-secret-info-block style="display: '.($displaySecretBlock ? 'block' : 'none').'">';

        echo $form->field($model, isset($index) ? "[{$index}]secret_part" : 'secret_part')->textInput(['value' => $getSecretValue('secret_part', $model, $entityFile), 'maxlength' => true])->label('Частина файлу, яка містить закриту інформацію');

        echo $form->field($model, isset($index) ? "[{$index}]secret_description" : 'secret_description')->textInput(['value' => $getSecretValue('secret_description', $model, $entityFile), 'maxlength' => true])->label('Причина неможливості публікації');

        echo '</div>';
    }
}


?>

<?php

$js = <<<JS

$(document).on('click', '[data-remove-file]', function(e) {
    
    var downloadBlock = $(this).closest('[data-file-container]').find('[data-download-block]');
    var fileBlock = $(this).closest('[data-file-container]').find('[data-file-block]');
    
    $(this).closest('[data-file-container]').find('[data-is-file-deleted]').val($(this).attr('data-remove-file'));
    
    $(downloadBlock).hide();
    $(fileBlock).show();
    
});

/*$(document).on('change', '[data-has-secret-info-checkbox]', function() {
        if ($(this).is(":checked")) {
            $('[data-secret-info-block]').show();
        } else {
            $('[data-secret-info-block]').hide();
            $('[data-secret-info-block] input').val('');
        }
});*/

JS;

$this->registerJs($js, $this::POS_READY);

?>
