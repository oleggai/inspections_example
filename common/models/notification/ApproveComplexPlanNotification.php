<?php

namespace common\models\notification;

use common\components\notification\NotificationComponent;
use common\helpers\Html;
use common\models\notification\channel\EmailChannel;
use common\models\notification\channel\ScreenChannel;
use common\models\notification\interfaces\NotifyInterface;
use common\models\PlanningPeriod;
use common\models\Subject;
use yii\base\InvalidArgumentException;
use yii\helpers\Url;

/**
 * Class ApproveComplexPlanNotification
 * @package common\models\notification
 */
class ApproveComplexPlanNotification extends Notification implements NotifyInterface
{
    const TYPE = 'approve_complex_plan';

    /**
     * @var null|PlanningPeriod
     */
    public $planningPeriod = null;

    /**
     * needed for validation
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return Notification
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $planningPeriod = key_exists('planningPeriod', $params) ? $params['planningPeriod'] : null;
        $subject = key_exists('subject', $params) ? $params['subject'] : null;

        if(!($planningPeriod instanceof PlanningPeriod)) {
            throw new InvalidArgumentException('planningPeriod must be set');
        }

        if(!($subject instanceof Subject)) {
            throw new InvalidArgumentException('subject must be set');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @param $key
     * @param array $params
     * @throws \yii\db\Exception
     */
    public static function createMultiple($key, $params = [])
    {
        $subjects = key_exists('subjects', $params) ? $params['subjects'] : null;
        $planningPeriod = key_exists('planningPeriod', $params) ? $params['planningPeriod'] : null;

        if(!$subjects) {
            throw new InvalidArgumentException('subjects can not be empty');
        }

        if(!$planningPeriod) {
            throw new InvalidArgumentException('planningPeriod can not be empty');
        }

        /* @var $notificationComponent NotificationComponent */
        $notificationComponent = \Yii::$app->notificationComponent;

        $rows_for_email = [];
        $columns_for_email = [
            'uuid',
            'title',
            'description',
            'route',
            'subject_id',
            'channel',
            'type'
        ];

        $rows_for_system = [];
        $columns_for_system = [
            'uuid',
            'title',
            'description',
            'route',
            'user_id',
            'channel',
            'type'
        ];

        /* @var $subject Subject */
        foreach ($subjects as $subject) {
            /* @var $notification self */
            $notification = self::create($key, ['subject' => $subject, 'planningPeriod' => $planningPeriod]);

            $rows_for_email[] = [
                $notification->uuid,
                $notification->getTitle(),
                $notification->getDescription(),
                serialize($notification->getRoute()),
                $subject->id,
                EmailChannel::TYPE,
                self::TYPE
            ];

            foreach ($subject->users as $user) {
                $rows_for_system[] = [
                    $notification->uuid,
                    $notification->getTitle(),
                    $notification->getDescription(),
                    serialize($notification->getRoute()),
                    $user->id,
                    ScreenChannel::TYPE,
                    self::TYPE
                ];
            }
        }

        if($notificationComponent->shouldSend((new ApproveComplexPlanNotification(['channel' => EmailChannel::TYPE])))) {
            // create notification to email
            \Yii::$app->db->createCommand()->batchInsert(self::tableName(), $columns_for_email, $rows_for_email)->execute();
        }

        if($notificationComponent->shouldSend((new ApproveComplexPlanNotification(['channel' => ScreenChannel::TYPE])))) {
            // create notification to system
            \Yii::$app->db->createCommand()->batchInsert(self::tableName(), $columns_for_system, $rows_for_system)->execute();
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return "Сповіщення суб'єкта про внесення його в комплексний план";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Ваше підприємство потрапило до проекту Плану здійснення комплексних заходів державного нагляду (контролю)';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $url = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());
        $url_without_code = \Yii::$app->urlManager->createAbsoluteUrl(['plan/complex', 'planningPeriodId' => $this->planningPeriod->id]);

        $format = <<<HTML
Ваше підприємство потрапило до %s Плану здійснення комплексних заходів державного нагляду (контролю) у %s році.<br>
Детальніше ознайомитися з %s Комплексного плану можна за посиланням: %s.<br>
У разі, якщо Ви хочете відмовитися від здійснення комплексного планового заходу державного нагляду (контролю) - перейдіть за посиланням в особистий кабінет на сайті %s або надішліть відповідне звернення до Державної регуляторної служби України напряму.<br>
Звертаємо Вашу увагу, що відмова від здійснення комплексного планового заходу - це відмова від початку здійснення планових заходів в один день, а не скасування проведення планових заходів у відповідному році.

HTML;

        return sprintf($format,
            Html::a('проекту', $url),
            $this->planningPeriod->year,
            Html::a('проектом', $url),
            Html::a($url_without_code, $url_without_code),
            Html::a(\Yii::$app->params['domainName'], \Yii::$app->params['domainName'])
        );

    }
    /**
     * @return array
     */
    public function getRoute()
    {
        return ['plan/complex', 'planningPeriodId' => $this->planningPeriod->id, 'code' => $this->subject->code];
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note2';
    }
}