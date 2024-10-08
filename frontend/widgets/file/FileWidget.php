<?php

namespace common\widgets\file;

use yii\base\Model;
use yii\base\Widget;

/**
 * Class FileWidget
 * @package common\components\widgets
 */
class FileWidget extends Widget
{
    /**
     * @var null
     */
    public $form = null;

    /**
     * @var $model Model
     */
    public $model = null;

    /**
     * @var null
     */
    public $attributeName = 'file';

    /**
     * @var string
     */
    public $delete_file_attribute = 'is_file_deleted';

    /**
     * @var string
     */
    public $has_ipn_attribute = 'has_ipn';

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

    public $file_type = null;
    public $file_type_attribute = 'file_type';

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        if($this->form == null) {
            throw new \Exception("Parameter 'form' is required");
        } elseif ($this->model == null) {
            throw new \Exception("Parameter 'model' is required");
        } elseif (!$this->file_type) {
            throw new \Exception("Parameter 'file_type' is required");
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('file', [
            'form' => $this->form,
            'model' => $this->model,
            'attributeName' => $this->attributeName,
            'options' => $this->options,
            'fieldOptions' => $this->fieldOptions,
            'index' => $this->index,
            'label' => $this->label ? : $this->model->getAttributeLabel($this->attributeName),
            'file_type' => $this->file_type,
            'delete_file_attribute' => $this->delete_file_attribute,
            'has_ipn_attribute' => $this->has_ipn_attribute,
            'file_type_attribute' => $this->file_type_attribute
        ]);
    }
}