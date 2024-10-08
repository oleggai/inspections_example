<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use common\models\subject\SubjectRequestsKo;
use yii\base\InvalidArgumentException;

/**
 * Class CreateComplaintNotification Скарга суб'єкта до КО (При створенні скарги відправляти сповіщення адмінстраторам КО)
 * @package common\models\notification
 */
class CreateComplaintNotification extends Notification implements NotifyInterface
{
    const TYPE = 'create_complaint';

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
        return 'Створення скарги';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = 'Створено скаргу %s субєктом господарювання %s';
        $complaintUrl = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());
        $subjectUrl = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $this->complaint->subject->id]);

        return sprintf($format, Html::a($complaintUrl, $complaintUrl), Html::a($this->complaint->subject->full_name, $subjectUrl));
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return ['subject-request-ko/view', 'id' => $this->complaint->id];
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Сповіщення адміністраторів КО про створення скарги суб'єктом";
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note3';
    }
}