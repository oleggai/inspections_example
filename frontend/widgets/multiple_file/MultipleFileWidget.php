<?php

namespace common\widgets\multiple_file;

use common\models\file\meta\BaseFile;
use common\widgets\ActiveForm;
use common\widgets\multiple_file\assets\MultipleFileAsset;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;

/**
 * Class MultipleFileWidget
 * @package common\widgets\file
 */
class MultipleFileWidget extends Widget
{
    const ENDPOINT_SUBJECT_COMPLAINT_CREATE = 'subject_complaint_create';
    const ENDPOINT_SUBJECT_COMPLAINT_KO_CREATE = 'subject_complaint_ko_create';

    /**
     * @var ActiveForm
     */
    public $form = null;

    /**
     * @var $model Model
     */
    public $model = null;

    /**
     * @var null
     */
    public $attributeName = 'files';

    /**
     * @var null
     */
    public $label = null;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $fieldOptions = [];

    public $index = null;

    public $endpoint = null;

    public $mockFiles = null;

    public $file_type = null;

    public $dropzoneSettings = [];

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if($this->form == null) {
            throw new InvalidConfigException("Parameter 'form' is required");
        } elseif ($this->model == null) {
            throw new InvalidConfigException("Parameter 'model' is required");
        } elseif (!$this->endpoint) {
            throw new InvalidConfigException("Parameter 'endpoint' is required");
        } elseif ($this->mockFiles === null) {
            throw new InvalidConfigException("Parameter 'mockFiles' is required");
        } elseif (!$this->file_type) {
            throw new InvalidConfigException("Parameter 'file_type' is required");
        }

        if(!key_exists('maxFilesize', $this->dropzoneSettings)) {
            $this->dropzoneSettings['maxFilesize'] = 50;
        }

        MultipleFileAsset::register($this->view);
    }

    /**
     * @return array
     */
    protected function prepareMockFiles()
    {
        $mockFiles = [];
        /* @var $mockFile BaseFile */
        foreach ($this->mockFiles as $mockFile) {
            $mockFiles[] = [
                'id' => $mockFile->id,
                'name' => $mockFile->original_name.'.'.$mockFile->ext, $mockFile->getUrl(),
                'size' => $mockFile->size,
                'url' => $mockFile->getUrl()
            ];
        }

        return $mockFiles;
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('files', [
            'form' => $this->form,
            'model' => $this->model,
            'attributeName' => $this->attributeName,
            'options' => $this->options,
            'fieldOptions' => $this->fieldOptions,
            'index' => $this->index,
            'label' => $this->label ? : $this->model->getAttributeLabel($this->attributeName),
            'endpoint' => $this->endpoint,
            'mockFiles' => $this->prepareMockFiles(),
            'file_type' => $this->file_type,
            'dropzoneSettings' => $this->dropzoneSettings
        ]);
    }
}