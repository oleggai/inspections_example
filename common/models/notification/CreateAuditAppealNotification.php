<?php


namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use frontend\models\audit\AuditAppeal;
use yii\base\InvalidArgumentException;

class CreateAuditAppealNotification extends Notification implements NotifyInterface
{
    const TYPE = 'create_audit_appeal';

    /**
     * @var null|AuditAppeal
     */
    public $auditAppeal = null;

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return Notification
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $auditAppeal = key_exists('auditAppeal', $params) ? $params['auditAppeal'] : null;

        if(!($auditAppeal instanceof AuditAppeal)) {
            throw new InvalidArgumentException('auditAppeal must be instance of AuditAppeal');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Створення запиту від суб\'єкта на аудит';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = 'Створено %s суб\'єкта господарювання %s';
        $audit_url = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());
        $subject_url = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $this->auditAppeal->subject_id]);

        return sprintf($format, Html::a('запит на аудит від су\'бєкта', $audit_url), Html::a($this->auditAppeal->subject->full_name, $subject_url));
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return ['audit/appeal-from-subject/view', 'id' => $this->auditAppeal->id];
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return 'Створення запиту від суб\'єкта на аудит';
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note3';
    }


}