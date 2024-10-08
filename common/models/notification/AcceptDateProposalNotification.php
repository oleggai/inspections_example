<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\ComplexInspectionDateProposal;
use common\models\notification\interfaces\NotifyInterface;
use yii\base\InvalidArgumentException;

/**
 * Class RemoveSubjectFromCpNotification Повідомлення ініціатору пропозиції що всі органи погодили запропоновану дату перевірки
 * @package common\models\notification
 */
class AcceptDateProposalNotification extends Notification implements NotifyInterface
{
    const TYPE = 'accept_date_proposal';

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
        return "Повідомлення для адміністратора органу-ініціатора про погодження всими органами запропнованої дати перевірки";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Всі органи погодили запропоновану Вами дату перевірки";
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = "Всі контролюючі органи погодили ініційовану Вами дату перевірки суб'єкта %s. Дата буде змінена в проекті комплексного плану";

        $query_link = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());

        return sprintf($format, Html::a($this->complexInspectionDateProposal->subject->full_name, $query_link));
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