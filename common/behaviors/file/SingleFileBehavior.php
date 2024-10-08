<?php

namespace common\behaviors\file;

use common\behaviors\log\ActiveLogBehavior;
use common\components\file\GeneratedFile;
use common\components\FileComponent;
use common\components\StorageConnector;
use common\models\AdditionalAttribute;
use common\models\file\meta\BaseFile;
use common\models\unplanned_reason\meta\UnplannedReason;
use common\workers\CompressPdfJob;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\validators\Validator;
use yii\web\UploadedFile;
use yii2tech\filestorage\BucketInterface;

/**
 * Class SingleFileBehavior
 * @package common\behaviors\file
 */
class SingleFileBehavior extends FileBehavior
{
    /**
     * @var bool
     */
    public $is_file_deleted = false;

    /**
     * @var bool
     */
    public $is_file_deleted_1 = false;

    /**
     * @var bool
     */
    public $is_file_deleted_2 = false;

    /**
     * @var bool
     */
    public $is_file_deleted_3 = false;

    /**
     * @var $file UploadedFile
     */
    public $file = null;

    public $file_1 = null;

    public $file_2 = null;

    public $file_3 = null;

    /**
     * @var null
     */
    public $file_type = null;

    /**
     * @var null
     */
    public $file_type_1 = null;

    /**
     * @var null
     */
    public $file_type_2 = null;

    /**
     * @var null
     */
    public $file_type_3 = null;

    /**
     * @var null
     */
    public $has_ipn = null;

    /**
     * @var null
     */
    public $has_ipn_1 = null;

    /**
     * @var null
     */
    public $has_secret_info = null;

    /**
     * @var null
     */
    public $secret_part = null;

    /**
     * @var null
     */
    public $secret_description = null;

    /**
     * @var FileComponent
     */
    protected $fileComponent = null;

    /**
     *
     */
    public function init()
    {
        $this->fileComponent = \Yii::$app->fileComponent;
        parent::init();
    }

    /**
     * @return array
     */
    public function getSafeAttributes()
    {
        return ['has_ipn', 'has_ipn_1', 'has_secret_info', 'secret_part', 'secret_description', 'is_file_deleted', 'is_file_deleted_1', 'is_file_deleted_2', 'is_file_deleted_3', 'file_type', 'file_type_1', 'file_type_2', 'file_type_3'];
    }

    /**
     * @param Model $owner
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $rules = [
            [['has_ipn', 'has_ipn_1', 'has_secret_info', 'secret_part', 'secret_description', 'is_file_deleted', 'is_file_deleted_1', 'is_file_deleted_2', 'is_file_deleted_3', 'file_type', 'file_type_1', 'file_type_2', 'file_type_3'], 'safe'],
/*            [['secret_part', 'secret_description'], 'required', 'when' => function ($model) use ($owner) {
                return $owner->has_secret_info == 1;
            }, 'whenClient' => "function (attribute, value) {
        return 0;
    }", 'message' => 'Будь ласка, заповніть це поле']*/
        ];

        $validators = $owner->validators;
        foreach ($rules as $rule) {
            if ($rule instanceof Validator) {
                $validators->append($rule);
                $this->validators[] = $rule;
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) {
                $validator = Validator::createValidator($rule[1], $owner, (array)$rule[0], array_slice($rule, 2));
                $validators->append($validator);
                $this->validators[] = $validator;
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
    }

    /**
     * @param null $file_path
     * @param null $file_type
     * @param string $file_attribute
     * @param string $file_type_attribute
     * @return bool
     */
    public function initFile($file_path = null, $file_type = null, $file_attribute = 'file', $file_type_attribute = 'file_type')
    {
        /* @var $owner Model */
        $owner = $this->owner;

        if($file_type) {
            $owner->{$file_type_attribute} = $file_type;
        }

        if($file_path) {
            $owner->{$file_attribute} = $this->fileComponent->getUploadedFile($file_path);
        } else {
            if(!($owner instanceof UnplannedReason)) {

                if($owner instanceof AdditionalAttribute) {
                    $owner->{$file_attribute} = UploadedFile::getInstance($owner, $owner->attribute_name.'[file]');
                } else {
                    $owner->{$file_attribute} = UploadedFile::getInstance($owner, $file_attribute);
                }
            }
        }

        return true;
    }

    /**
     * @param null $filePath
     * @param null $file_type
     * @param string $file_attribute
     * @param string $delete_file_attribute
     * @param string $file_type_attribute
     * @param string $has_ipn_attribute
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveFile($filePath = null, $file_type = null, $file_attribute = 'file', $delete_file_attribute = 'is_file_deleted', $file_type_attribute = 'file_type', $has_ipn_attribute = 'has_ipn')
    {
        $s = DIRECTORY_SEPARATOR;

        /* @var $owner Model */
        $owner = $this->owner;

        $this->initFile($filePath, $file_type, $file_attribute, $file_type_attribute);

        $entityFile = $owner->{$file_type_attribute} ? BaseFile::instantiate($owner->{$file_type_attribute})->getEntityFile($this->owner) : null;

        if ($entityFile) {
            $entityFile->has_ipn = $owner->{$has_ipn_attribute} ? : null;
            $entityFile->has_secret_info = $owner->has_secret_info ? : null;
            $entityFile->secret_part = $owner->secret_part ? : null;
            $entityFile->secret_description = $owner->secret_description ? : null;
        }

        if ($owner->{$file_attribute}) {

            $storage = $this->storageConnector->getStorage($entityFile);

            /* @var $bucket BucketInterface */
            $bucket = $storage->getBucket(StorageConnector::BUCKET_INSPECTION);

            $pathTemp = \Yii::getAlias('@files') . $s;

            if ($entityFile->isNewRecord) {

                $entity_name = $this->fileComponent->generateRandomName();
                $entityFile->name = $entity_name;

                $entityFile->storage = StorageConnector::CURRENT_STORAGE;

                $fileName = $entity_name . '.' . $owner->{$file_attribute}->extension;

                if($owner->{$file_attribute}->saveAs($pathTemp . $fileName)) {

                    // detect ext
                    $ext = pathinfo($pathTemp . $fileName, PATHINFO_EXTENSION);
                    $entityFile->ext = $ext;

                    // detect mime_type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $pathTemp . $fileName);
                    $entityFile->mime_type = $mime_type;

                    // detect file size
                    $file_size = filesize($pathTemp . $fileName);
                    $entityFile->size = $file_size;

                    if(@$bucket->moveFileIn($pathTemp . $fileName, $fileName)) {
                        if (!$entityFile->save()) {
                            $bucket->deleteFile($fileName);
                            throw new \Exception('123');
                            return false;
                        }
                    } else {
                        unlink($pathTemp . $fileName);
                        throw new \Exception('124');
                        return false;
                    }

                } else {
                    throw new \Exception('125');
                    return false;
                }

            } else {

                $fileNameOld = $entityFile->name . '.' . $entityFile->ext;
                $fileNameNew = $entityFile->name . '.' . $owner->{$file_attribute}->extension;

                if ($owner->{$file_attribute}->saveAs($pathTemp . $fileNameNew)) {

                    // detect ext
                    $ext = pathinfo($pathTemp . $fileNameNew, PATHINFO_EXTENSION);
                    $entityFile->ext = $ext;

                    // detect mime_type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $pathTemp . $fileNameNew);
                    $entityFile->mime_type = $mime_type;

                    // detect file size
                    $file_size = filesize($pathTemp . $fileNameNew);
                    $entityFile->size = $file_size;

                    if(@$bucket->moveFileIn($pathTemp . $fileNameNew, $fileNameNew)) {
                        if($entityFile->save()) {
                            if ($fileNameNew !== $fileNameOld) {
                                $bucket->deleteFile($fileNameOld);
                            }
                        } else {
                            // 2 files
                            if ($fileNameNew !== $fileNameOld) {
                                $bucket->deleteFile($fileNameNew);
                            }

                            return false;
                        }
                    } else {
                        unlink($pathTemp . $fileNameNew);
                        return false;
                    }

                } else {
                    return false;
                }
            }

            if($entityFile->ext == 'pdf') {
                \Yii::$app->queue->push(new CompressPdfJob([
                    'entityFileId' => $entityFile->id
                ]));
            }

        } else {

            if($entityFile) {
                if(!$entityFile->isNewRecord) {

                    $has_active_log_behavior = false;
                    foreach ($entityFile->behaviors as $behavior) {
                        if($behavior instanceof ActiveLogBehavior) {
                            $has_active_log_behavior = true;
                            break;
                        }
                    }

                    $secret_attributes = ['has_ipn', 'has_secret_info', 'secret_part', 'secret_description'];

                    if($has_active_log_behavior) {
                        $entityFile->updateAttributesWithLog($secret_attributes);
                    } else {
                        $entityFile->updateAttributes($secret_attributes);
                    }
                }
            }

            if ($owner->{$delete_file_attribute}) {
                $entityFile = BaseFile::findOne(['id' => $owner->{$delete_file_attribute}]);

                $storage = $this->storageConnector->getStorage($entityFile);

                /* @var $bucket BucketInterface */
                $bucket = $storage->getBucket(StorageConnector::BUCKET_INSPECTION);

                if ($entityFile) {

                    // TODO: file is deleted in afterDelete() method. Maybe deleting file here is redundant. Check it
                    if($bucket->deleteFile($entityFile->name . '.' . $entityFile->ext)) {
                        if(!$entityFile->delete()) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }
        }

        return $entityFile ? : true;
    }
}