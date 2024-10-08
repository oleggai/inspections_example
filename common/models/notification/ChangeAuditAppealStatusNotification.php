<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use frontend\models\audit\AuditAppeal;
use yii\base\InvalidArgumentException;

class ChangeAuditAppealStatusNotification extends Notification implements NotifyInterface
{
    const TYPE = 'change_audit_appeal';

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
        return 'Зміна статусу запиту на створення аудиту';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = 'Статус запиту на %s змінено';
        $audit_url = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());
        return sprintf($format, Html::a('аудит', $audit_url));
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return ['audit/appeal/view', 'id' => $this->auditAppeal->id];
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return 'Зміна статусу запиту на створення аудиту';
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note3';
    }


}