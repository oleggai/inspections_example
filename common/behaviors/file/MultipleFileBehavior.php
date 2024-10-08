<?php

namespace common\behaviors\file;

use common\components\FileComponent;
use common\components\StorageConnector;
use common\helpers\ArrayHelper;
use common\models\file\meta\BaseFile;
use common\workers\CompressPdfJob;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\validators\Validator;
use yii2tech\filestorage\BucketInterface;

/**
 * Class MultipleFileBehavior
 * @package common\behaviors\file
 */
class MultipleFileBehavior extends FileBehavior
{
    /**
     * @var string
     */
    public $endpoint = '';

    /**
     * @var string
     */
    public $multiple_file_type = '';

    /**
     * @var string
     */
    public $deleted_multiple_file_ids = '';

    /**
     * @return array
     */
    public function getSafeAttributes()
    {
        return ['endpoint', 'deleted_multiple_file_ids', 'multiple_file_type'];
    }

    /**
     * @param Model $owner
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $rules = [
            [['endpoint', 'deleted_multiple_file_ids', 'multiple_file_type'], 'safe']
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
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveFiles()
    {
        $s = DIRECTORY_SEPARATOR;

        // сохраняем то что в temp, если пдф то компресим
        $files = FileComponent::getTempFiles($this->endpoint);

        $pathTemp = \Yii::getAlias('@files') . $s;

        foreach($files as $file) {
            $entityFile = BaseFile::instantiate($this->multiple_file_type);
            $entityFile->{$entityFile::ENTITY_ID} = $this->owner->id;

            $storage = $this->storageConnector->getStorage($entityFile);

            /* @var $bucket BucketInterface */
            $bucket = $storage->getBucket(StorageConnector::BUCKET_INSPECTION);

            $entity_name = $this->fileComponent->generateRandomName();
            $entityFile->name = $entity_name;

            $entityFile->storage = StorageConnector::CURRENT_STORAGE;

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $fileName = $entity_name . '.' . $ext;

            $original_name = explode('_original_name_', basename($file))[0];
            $entityFile->original_name = $original_name;

            if(rename($file,$pathTemp . $fileName)) {

                $entityFile->ext = $ext;

                // detect mime_type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $pathTemp . $fileName);
                $entityFile->mime_type = $mime_type;

                // detect file size
                $file_size = filesize($pathTemp . $fileName);
                $entityFile->size = $file_size;

                if($bucket->moveFileIn($pathTemp . $fileName, $fileName)) {
                    if (!$entityFile->save()) {
                        $bucket->deleteFile($fileName);
                        return false;
                    }
                } else {
                    unlink($pathTemp . $fileName);
                    return false;
                }

            } else {
                return false;
            }

            if($entityFile->ext == 'pdf') {
                \Yii::$app->queue->push(new CompressPdfJob([
                    'entityFileId' => $entityFile->id
                ]));
            }
        }

        // удаляем то что в $deleted_multiple_file_ids
        $deletedIds = explode(',', $this->deleted_multiple_file_ids);
        $deletedIds = array_unique($deletedIds);
        $deletedIds = ArrayHelper::clearEmpty($deletedIds);
        if($deletedIds) {
            $entityFiles = BaseFile::find()
                ->where(['id' => $deletedIds])
                ->all();
            foreach ($entityFiles as $entityFile) {
                $storage = $this->storageConnector->getStorage($entityFile);

                /* @var $bucket BucketInterface */
                $bucket = $storage->getBucket(StorageConnector::BUCKET_INSPECTION);

                if ($entityFile) {

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
    }
}