<?php

namespace common\widgets\switchbtn;

use common\widgets\switchbtn\assets\SwitchAsset;
use frontend\assets\BootboxSourceAsset;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * Class SwitchWidget
 * @package common\widgets\switch
 */
class SwitchWidget extends Widget
{

    public $value = false;

    // юзать когда активное и неактивное значение не равно 0 и 1, а равно например 2 и 3. Пример на странице редактирования инспекции
    public $checked_values = [];
    public $unchecked_values = [];

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
    public $attributeName = null;

    /**
     * @var null
     */
    public $label = null;

    public $text_active = 'Відповідальний';

    public $text_passive = 'Не відповідальний';

    /**
     * @var string
     */
    public $tooltip_passive = 'Надати права відповідального співробітника по органу';

    /**
     * @var string
     */
    public $tooltip_active = 'Відмінити права відповідального співробітника по органу';

    /**
     * @var bool
     */
    public $confirm = false;

    /**
     * @var string
     */
    public $confirm_active = 'Ви впевнені, що хочете зняти права відповідального в органі з даного користувача?';

    /**
     * @var string
     */
    public $confirm_passive = 'Ви впевнені, що хочете надати даному користувачу права відповідального в органі?';

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var string
     */
    public $block_class = '';

    /**
     *
     */
    public function init()
    {
        parent::init();

        SwitchAsset::register($this->view);

        if($this->confirm) {
            BootboxSourceAsset::register($this->view);
            $js = <<<JS
SWITCH.processUser();
$('body').addClass('subj_page');
JS;
            $this->view->registerJs($js, $this->view::POS_READY);
        }

        $js = <<<JS
SWITCH.processTooltip();
JS;
        $this->view->registerJs($js, $this->view::POS_READY);

    }


    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public function run()
    {
        $checkboxId = \Yii::$app->security->generateRandomString(6);
        $checkboxOptions = ['class' => '_toggle', 'data-checkbox-toggle' => '', 'id' => $checkboxId];

        $confirmMessage = '';
        if($this->confirm) {
            $confirmMessage = $this->value ? $this->confirm_active : $this->confirm_passive;
        }
        $checked = $this->value;
        $title = $this->value ? $this->tooltip_active : $this->tooltip_passive;
        if($this->checked_values) {
            if(in_array($this->value, $this->checked_values)) {
                $checked = true;
                $title = $this->tooltip_active;
                $confirmMessage = $this->confirm_active;
            } else {
                $checked = false;
                $title = $this->tooltip_passive;
                $confirmMessage = $this->confirm_passive;
            }
        }
        $checkboxInput = Html::checkbox('', $checked, $checkboxOptions);

        if($this->form) {
            $checkboxInput = $this->form->field($this->model, $this->attributeName)
                ->checkbox($checkboxOptions)
                ->label($this->label ? : $this->model->getAttributeLabel($this->attributeName));
        }

        $tooltipActive = $this->tooltip_active;
        $tooltipPassive = $this->tooltip_passive;

        $url = Url::to($this->url);

        $textActive = $this->text_active;
        $textPassive = $this->text_passive;

        $blockClass = $this->block_class ? 'class="' . $this->block_class . '"' : '';

        $html = <<<HTML
<div data-switch-widget-container $blockClass>
						$checkboxInput
						<label for="$checkboxId" 
						data-toggle="tooltip" 
						data-container="body" 
						data-placement="top" 
						data-title="$title" 
						data-tooltip-active="$tooltipActive" data-tooltip-passive="$tooltipPassive" 
						data-switch-confirm-message="$confirmMessage"
						data-url="$url"
						>
							<span class="off">$textPassive</span>
							<span class="on">$textActive</span>
						</label>
					</div>
HTML;

        return $html;
    }
}