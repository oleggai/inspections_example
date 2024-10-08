<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\audit\Audit;
use common\models\notification\interfaces\NotifyInterface;
use yii\base\InvalidArgumentException;

/**
 * Class CreateAuditNotification Сповіщення про створення запиту на аудит (відправляється керівникам та адмінам КО)
 * @package common\models\notification
 */
class CreateAuditNotification extends Notification implements NotifyInterface
{
    const TYPE = 'create_audit';

    /**
     * @var null|Audit
     */
    public $audit = null;

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return Notification
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $audit = key_exists('audit', $params) ? $params['audit'] : null;

        if(!($audit instanceof Audit)) {
            throw new InvalidArgumentException('audit must be instance of Audit');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Створення запиту на аудит';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = 'Створено %s суб\'єкта господарювання %s';
        $audit_url = \Yii::$app->urlManager->createAbsoluteUrl($this->getRoute());
        $subject_url = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $this->audit->subject_id]);

        return sprintf($format, Html::a('запит на аудит', $audit_url), Html::a($this->audit->subject->full_name, $subject_url));
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return ['audit/audit/view', 'id' => $this->audit->id];
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Створення запиту на аудит";
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note3';
    }
}