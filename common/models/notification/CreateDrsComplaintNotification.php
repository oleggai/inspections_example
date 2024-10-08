<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use common\models\subject\SubjectRequests;
use yii\base\InvalidArgumentException;

/**
 * Class CreateDrsComplaintNotification Скарга суб'єкта до ДРС (При створенні скарги відправляти сповіщення про це керівникам ДРС)
 * @package common\models\notification
 */
class CreateDrsComplaintNotification extends Notification implements NotifyInterface
{
    const TYPE = 'create_drs_complaint';

    /**
     * @var null|SubjectRequests
     */
    public $complaint = null;

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return static
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
     * @return string
     */
    public function getLabel()
    {
        return "Скарга суб'єкта керівникам ДРС";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Створення скарги до ДРС';
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
        return ['subject-request/complaint-view', 'id' => $this->complaint->id];
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note3';
    }
}