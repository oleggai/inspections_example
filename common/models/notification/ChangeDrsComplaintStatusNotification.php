<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use common\models\subject\SubjectRequests;
use common\models\subject\SubjectRequestsKo;
use yii\base\InvalidArgumentException;

/**
 * Class ChangeDrsComplaintStatusNotification Сповіщення субєкта про зміну статусу скарги ДРС-ом
 * @package common\models\notification
 */
class ChangeDrsComplaintStatusNotification extends Notification implements NotifyInterface
{
    const TYPE = 'change_drs_complaint_status';

    /**
     * @var null|SubjectRequests
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

        if(!($complaint instanceof SubjectRequests)) {
            throw new InvalidArgumentException('complaint must be instance of SubjectRequests');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Сповіщення суб'єкта про зміну статусу скарги Державною регуляторною службою";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Зміна статусу скарги, надісланої до ДРС';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = 'Стан розгляду Вашої скарги %s змінився. Наразі вона "%s".';
        $complaintUrl = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());

        return sprintf($format, Html::a($complaintUrl, $complaintUrl), SubjectRequestsKo::getStatusLabels()[$this->complaint->status]);
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return ['subject/view/complaints', 'subject_id' => $this->complaint->subject->id, 'id' => $this->complaint->id];
    }
}