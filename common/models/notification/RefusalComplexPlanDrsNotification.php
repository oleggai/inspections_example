<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\file\entities\GeneratedRefusalFile;
use common\models\file\meta\BaseFile;
use common\models\notification\interfaces\NotifyInterface;
use common\models\PlanningPeriod;
use common\models\subject\SubjectRequests;
use yii\base\InvalidArgumentException;

/**
 * Class RefusalComplexPlanNotification Відмова суб'єкта від КП, відправка керівникам ДРС
 * @package common\models\notification
 */
class RefusalComplexPlanDrsNotification extends Notification implements NotifyInterface
{
    const TYPE = 'refusal_complex_plan';

    /**
     * @var null|SubjectRequests
     */
    public $complaint = null;

    /**
     * @var null|PlanningPeriod
     */
    public $planningPeriod = null;

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

        $planningPeriod = key_exists('planningPeriod', $params) ? $params['planningPeriod'] : null;

        if(!($planningPeriod instanceof PlanningPeriod)) {
            throw new InvalidArgumentException('planningPeriod must be instance of PlanningPeriod');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Сповіщення керівників ДРС про відмову суб'єкта від КП";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Звернення про відмову від проведення комплексного планового заходу державного нагляду (контролю)';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = '<p>Повідомляємо Вас, що %s, код ЄДРПОУ %s, відмовляється від проведення комплексного планового заходу державного нагляду (контролю).</p> <p>Просимо Вас виключити %s, код ЄДРПОУ %s, з Плану здійснення комплексних заходів державного нагляду (контролю) органів державного нагляду (контролю) на %s рік</p><br>
<p>Підпис та файл за цим лінком: %s</p>';
        $subject = $this->complaint->subject;
        $subject_link = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $subject->id]);

        /* @var $refusalFile GeneratedRefusalFile */
        $refusalFile = $this->complaint->getFiles()->andWhere([BaseFile::tableName().'.type' => GeneratedRefusalFile::TYPE])->one();
        $archive_link = \Yii::$app->urlManager->createAbsoluteUrl(['eds/download-archive', 'file_id' => $refusalFile->id]);

        return sprintf($format, Html::a($subject->full_name, $subject_link), $subject->code, Html::a($subject->full_name, $subject_link), $subject->code, $this->planningPeriod->year, Html::a($archive_link, $archive_link));
    }

    public function getRoute()
    {
        // TODO: Implement getRoute() method.
    }
}