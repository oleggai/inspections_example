<?php

namespace common\behaviors\file;

use common\components\FileComponent;
use common\components\StorageConnector;
use common\models\AdditionalAttribute;
use common\models\AnnualInspection;
use common\models\Article;
use common\models\ComplexPlan;
use common\models\ComplexPlanChange;
use common\models\file\entities\ComplaintKoFile;
use common\models\file\meta\BaseFile;
use common\models\InspectionDocument;
use common\models\InspectionReason;
use common\models\PlanChange;
use common\models\PlanProject;
use common\models\Report;
use common\models\subject\SubjectAppeal;
use common\models\subject\SubjectRequests;
use common\models\subject\SubjectRequestsKo;
use common\models\TrainingMaterial;
use common\models\unplanned_reason\meta\UnplannedReason;
use yii\base\Behavior;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class FileBehavior
 * @package common\behaviors\file
 */
class FileBehavior extends Behavior
{
    /**
     * @var FileComponent
     */
    protected $fileComponent = null;

    /**
     * @var StorageConnector
     */
    protected $storageConnector = null;

    /**
     *
     */
    public function init()
    {
        $this->fileComponent = \Yii::$app->fileComponent;
        $this->storageConnector = \Yii::$app->storageConnector;

        parent::init();
    }

    /**
     * track references of appended validators
     * @var \yii\validators\Validator[]
     */
    protected $validators = [];

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDeleteEntity',
        ];
    }

    /**
     * @param $event
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function beforeDeleteEntity($event)
    {
        /* @var $owner Model */
        $owner = $this->owner;

        $files = $owner->files;

        /* @var $file BaseFile */
        foreach ($files as $file) {
            $file->delete();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        /* @var $owner Model */
        $owner = $this->owner;

        if ($owner instanceof AnnualInspection) {

            return $owner->hasMany(BaseFile::class, ['inspection_id' => 'id']);

        } elseif ($owner instanceof UnplannedReason) {

            return $owner->hasMany(BaseFile::class, ['unplanned_reason_id' => 'id']);

        } elseif ($owner instanceof InspectionReason) {

            return $owner->hasMany(BaseFile::class, ['inspection_reason_id' => 'id']);

        } elseif ($owner instanceof InspectionDocument) {

            return $owner->hasMany(BaseFile::class, ['inspection_document_id' => 'id']);

        } elseif ($owner instanceof Report) {

            return $owner->hasMany(BaseFile::class, ['report_id' => 'id']);

        } elseif ($owner instanceof ComplexPlan) {

            return $owner->hasMany(BaseFile::class, ['complex_plan_id' => 'id']);

        } elseif ($owner instanceof ComplexPlanChange) {

            return $owner->hasMany(BaseFile::class, ['complex_plan_change_id' => 'id']);

        } elseif ($owner instanceof PlanChange) {

            return $owner->hasMany(BaseFile::class, ['plan_change_id' => 'id']);

        } elseif ($owner instanceof PlanProject) {

            return $owner->hasMany(BaseFile::class, ['plan_project_id' => 'id']);

        } elseif ($owner instanceof SubjectRequests) {

            return $owner->hasMany(BaseFile::class, ['subject_request_id' => 'id']);

        } elseif ($owner instanceof Article) {

            return $owner->hasMany(BaseFile::class, ['article_id' => 'id']);

        } elseif ($owner instanceof AdditionalAttribute) {

            return $owner->hasMany(BaseFile::class, ['additional_attribute_id' => 'id']);

        } elseif ($owner instanceof SubjectAppeal) {

            return $owner->hasMany(BaseFile::class, ['subject_appeal_id' => 'id']);

        } elseif ($owner instanceof TrainingMaterial) {

            return $owner->hasMany(BaseFile::class, ['training_material_id' => 'id']);

        } elseif ($owner instanceof SubjectRequestsKo) {

            return $owner->hasMany(BaseFile::class, ['subject_request_id' => 'id']);
        }
    }

    /**
     *
     */
    public function detach()
    {
        $ownerValidators = $this->owner->validators;
        $cleanValidators = [];
        foreach ($ownerValidators as $validator) {
            if (!in_array($validator, $this->validators)) {
                $cleanValidators[] = $validator;
            }
        }

        $ownerValidators->exchangeArray($cleanValidators);

        parent::detach();
    }
}