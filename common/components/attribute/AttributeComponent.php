<?php

namespace common\components\attribute;

use common\components\FormComponent;
use common\models\AdditionalAttribute;
use common\models\file\entities\AdditionalAttributeFile;
use common\widgets\ActiveForm;
use common\widgets\file\FileWidget;
use frontend\widgets\switch_btn\SwitchWidget;
use frontend\widgets\toggle\ToggleWidget;
use kartik\select2\Select2;
use yii\base\Component;

/**
 * Class AttributeComponent
 * @package common\components\attribute
 */
class AttributeComponent extends Component
{
    const FORM_HIDDEN = 'form_hidden';
    const FORM_TEXT_INPUT = 'form_text_input';
    const FORM_TEXTAREA = 'form_textarea';
    const FORM_SELECT = 'form_select';
    const FORM_YES_NO = 'form_yes_no';
    const FORM_TOGGLE = 'form_toggle';
    const FORM_DATE = 'form_date';
    const FORM_FILE = 'form_file';

    const TYPE_ATTRIBUTE = 'attribute';
    const TYPE_GROUP_LABEL = 'group_label';
    const TYPE_BLOCK_NAME = 'block_name';

    const NO = 1;
    const YES = 2;
    const ANOTHER = 'another';

    protected $validation_required_message = 'Будь ласка, заповніть це поле';
    /**
     * @var array Тут завжди массив нових моделей AdditionalAttribute проіндексований по attribute_name.
     * При зберіганні перевіряти існування такого атрибуту в базі
     */
    protected $_models = [];

    /**
     * @return array
     */
    protected function addCountRule()
    {
        return [
            [
                'validator' => 'integer',
                'options' => [
                    'message' => 'Це значення має бути цілим числом'
                ],
            ],
            [
                'validator' => 'compare',
                'options' => [
                    'skipOnEmpty' => true,
                    'compareValue' => 0,
                    'operator' => '>=',
                    'type' => 'number',
                    'message' => 'Це значення має бути більшим або рівним 0'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function addDoubleRule()
    {
        return [
            [
                'validator' => 'double',
                'options' => [
                    'message' => 'Це значення має бути цілим або флоат числом. Наприклад 201.44 або 201'
                ],
            ],
            [
                'validator' => 'compare',
                'options' => [
                    'skipOnEmpty' => true,
                    'compareValue' => 0,
                    'operator' => '>=',
                    'type' => 'number',
                    'message' => 'Це значення має бути більшим або рівним 0'
                ]
            ]
        ];
    }

    /**
     * @param $model
     * @param $attribute_name
     * @return mixed|string
     * @deprecated
     * Повертає значення атрибуту (значення бере з поста якщо є, якщо нема то )
     */
    protected function getValue($model, $attribute_name)
    {
        $post = \Yii::$app->request->post();

        if ($post) {
            $additional_attribute_data = isset($post['AdditionalAttribute']) ? $post['AdditionalAttribute'] : [];
            $post_value = isset($additional_attribute_data[$attribute_name]) ? $additional_attribute_data[$attribute_name] : '';

            return $post_value;
        } else {
            return $model->{$attribute_name};
        }
    }

    /**
     * @return array
     */
    public function getModels()
    {
        return $this->_models;
    }

    /**
     * @param AdditionalAttribute $model
     */
    public function addModel(AdditionalAttribute $model)
    {
        $this->_models[$model->attribute_name] = $model;
    }

    /**
     * @param ActiveForm $form
     * @param $attribute_name
     * @param $attributes
     * @param bool $without_label
     * @return string
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function renderField(ActiveForm $form, $attribute_name, $attributes, $without_label = false)
    {
        /* @var $formComponent FormComponent */
        $formComponent = \Yii::$app->formComponent;

        /**
         * @param $field
         * @param $attributeData
         * @return string
         */
        $wrap = function ($field, $attributeData) {

            $dependsOn = $attributeData['depends_on'] ?? null;
            $showValue = $attributeData['show_value'] ?? null;

            $showValue = json_encode($showValue);

            return $dependsOn ? "<div data-depends-on='$dependsOn' data-show-value='$showValue'>$field</div>" : $field;
        };

        $models = $this->getModels();

        if (!array_key_exists($attribute_name, $models)) {
            return '';
        }

        $model = $models[$attribute_name];
        $attributeData = $attributes[$attribute_name];

        if(isset($attributeData['default_value']) && $attributeData['default_value'] && !$model->{$attribute_name}) {
            $model->{$attribute_name} = $attributeData['default_value'];
        }

        $field = $form->field($model, $attribute_name);

        $commonOptions = [
            'data-attribute' => $attribute_name
        ];

        $form_type = $attributeData['form_type'] ?? null;

        $label = $without_label ? false : $attributeData['label'];

        switch ($form_type) {

            case self::FORM_HIDDEN:

                $field = $field->hiddenInput()->label($label);

                break;

            case self::FORM_SELECT:

                $field = $field->widget(Select2::class, [
                    'data' => $attributeData['data_list'],
                    'options' => array_merge(['placeholder' => 'Виберіть...', 'value' => $model->value], $commonOptions),
                    'hideSearch' => $attributeData['hideSearch'] ?? true,
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ])->label($label);

                break;
            case self::FORM_YES_NO:

                $field = SwitchWidget::widget([
                    'form' => $form,
                    'model' => $model,
                    'attribute' => $attribute_name,
                    'label' => $label,
                    'items' => $attributeData['data_list'],
                    'default_value' => self::NO,
                    'options' => array_merge([], $commonOptions)
                ]);

                break;

            case self::FORM_TOGGLE:

                $field = ToggleWidget::widget([
                    'form' => $form,
                    'model' => $model,
                    'attribute' => $attribute_name,
                    'true_value' => self::YES,
                    'false_value' => self::NO,
                    'options' => array_merge([], $commonOptions)
                ]);

                $field = <<<HTML
<div class="custom-switch-block">
$field
<span class="custom-switch-text"><b>$label</b></span>
</div>

HTML;


                break;

            case self::FORM_DATE:

                $field = $formComponent->renderDate($form, $model, $attribute_name, $label, null, $commonOptions);

                break;

            case self::FORM_FILE:

                $field = FileWidget::widget([
                    'form' => $form,
                    'model' => $model,
                    'label' => $label,
                    'file_type' => AdditionalAttributeFile::TYPE,
                    'index' => $attribute_name
                ]);

                break;

            case self::FORM_TEXTAREA:

                $field = $field->textarea(array_merge(['value' => $model->value], $commonOptions))->label($label);

                break;

            default:
                $field = $field->textInput(array_merge(['value' => $model->value], $commonOptions))->label($label);
        }

        return $wrap($field, $attributeData);
    }

    /**
     * @param $attribute_name
     * @param $attributes
     * @return string
     */
    public function getLabel($attribute_name, $attributes)
    {
        if (!array_key_exists($attribute_name, $attributes)) {
            return '';
        }

        $wrap = function ($label, $attributeData) {

            $dependsOn = $attributeData['depends_on'] ?? null;
            $showValue = $attributeData['show_value'] ?? null;

            $showValue = json_encode($showValue);

            return $dependsOn ? "<div data-depends-on='$dependsOn' data-show-value='$showValue'>$label</div>" : $label;
        };

        return $wrap($attributes[$attribute_name]['label'], $attributes[$attribute_name]);
    }

    /**
     * @return array
     */
    protected function getYesNoList()
    {
        return [self::YES => 'Так', self::NO => 'Ні'];
    }
}