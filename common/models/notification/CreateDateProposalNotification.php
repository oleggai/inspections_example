<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\ComplexInspectionDateProposal;
use common\models\notification\interfaces\NotifyInterface;
use yii\base\InvalidArgumentException;

/**
 * Class RemoveSubjectFromCpNotification Повідомлення адмінам КО які входять до комплексної перевірки про створення пропозиції на зміну дати перевірки
 * @package common\models\notification
 */
class CreateDateProposalNotification extends Notification implements NotifyInterface
{
    const TYPE = 'create_date_proposal';

    /**
     * @var null|ComplexInspectionDateProposal
     */
    public $complexInspectionDateProposal = null;

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return static
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $complexInspectionDateProposal = key_exists('complexInspectionDateProposal', $params) ? $params['complexInspectionDateProposal'] : null;

        if(!($complexInspectionDateProposal instanceof ComplexInspectionDateProposal)) {
            throw new InvalidArgumentException('complexInspectionDateProposal must be instance of ComplexInspectionDateProposal');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Повідомлення для адміністраторів КО про створення запиту на зміну дати комплексної перевірки";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Новий запит на зміну дати перевірки";
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = "Орган %s створив %s на зміну дати перевірки суб'єкта господарювання %s";

        $query_link = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());
        $regulator_link = \Yii::$app->urlManager->createAbsoluteUrl(['regulator/view', 'id' => $this->complexInspectionDateProposal->regulator_id]);
        $subject_link = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $this->complexInspectionDateProposal->subject_id]);

        return sprintf($format, Html::a($this->complexInspectionDateProposal->regulator->name, $regulator_link), Html::a('запит', $query_link), Html::a($this->complexInspectionDateProposal->subject->full_name, $subject_link));
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        $planInspection = $this->complexInspectionDateProposal->getComplexInspection();

        return ['plan-inspection/date-proposal', 'id' => $planInspection->id];
    }
}