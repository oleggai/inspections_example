<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\notification\interfaces\NotifyInterface;
use common\models\PlanningPeriod;
use common\models\Subject;
use yii\base\InvalidArgumentException;

/**
 * Class RemoveSubjectFromCpNotification Повідомлення суб'єктам та адмінам КО про видалення ДРС-ом суб'єкта із КП
 * @package common\models\notification
 */
class RemoveSubjectFromCpNotification extends Notification implements NotifyInterface
{
    const TYPE = 'remove_subject_from_cp';

    /**
     * @var null|PlanningPeriod
     */
    public $planning_period = null;

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return static
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $subject = key_exists('subject', $params) ? $params['subject'] : null;

        if(!($subject instanceof Subject)) {
            throw new InvalidArgumentException('subject must be instance of Subject');
        }

        $planning_period = key_exists('planning_period', $params) ? $params['planning_period'] : null;

        if(!($planning_period instanceof PlanningPeriod)) {
            throw new InvalidArgumentException('planning_period must be instance of PlanningPeriod');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Повідомлення суб'єкта та адміністраторів КО про видалення суб'єкта із КП ДРС-ом";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Видалення суб'єкту господарювання із комплексного плану";
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if($this->key == self::KEY_TO_SUBJECT) {
            $format = "Ваш суб'єкт господарювання %s видалений із Проекту плану здійснення комплексних заходів державного нагляду (контролю) на %s рік";
        } else {
            $format = "Суб'єкт господарювання %s видалений із Проекту плану здійснення комплексних заходів державного нагляду (контролю) на %s рік";
        }

        $subject_link = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $this->subject->id]);

        return sprintf($format, Html::a($this->subject->full_name, $subject_link), $this->planning_period->year);
    }

    public function getRoute()
    {
        // TODO: Implement getRoute() method.
    }
}