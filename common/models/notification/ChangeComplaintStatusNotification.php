<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use common\models\subject\SubjectRequestsKo;
use yii\base\InvalidArgumentException;

/**
 * Class ChangeComplaintStatusNotification Сповіщення субєкта про зміну статусу скарги контролюючим органом
 * @package common\models\notification
 */
class ChangeComplaintStatusNotification extends Notification implements NotifyInterface
{
    const TYPE = 'change_complaint_status';

    /**
     * @var null|SubjectRequestsKo
     */
    public $complaint = null;

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return Notification
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $complaint = key_exists('complaint', $params) ? $params['complaint'] : null;

        if(!($complaint instanceof SubjectRequestsKo)) {
            throw new InvalidArgumentException('complaint must be instance of SubjectRequestsKo');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Зміна статусу скарги';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = 'Стан розгляду Вашої скарги №%s змінився. Наразі вона має статус "%s".';
        $complaintUrl = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());

        return sprintf($format, Html::a($this->complaint->id, $complaintUrl), SubjectRequestsKo::getStatusLabels()[$this->complaint->status]);
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return ['/subject/view/complaints-ko/view', 'id' => $this->complaint->id];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return "Сповіщення суб'єкта про зміну статусу скарги контролюючим органом";
    }
}
