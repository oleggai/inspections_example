<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\file\entities\GeneratedRefusalFile;
use common\models\file\meta\BaseFile;
use common\models\notification\interfaces\NotifyInterface;
use common\models\subject\SubjectRequests;
use yii\base\InvalidArgumentException;

/**
 * Class ChangeDrsRefusalStatusNotification Зміна ДРС-ом статусу відмови суб'єкта від КП
 * @package common\models\notification
 */
class ChangeDrsRefusalStatusNotification extends Notification implements NotifyInterface
{
    const TYPE = 'change_drs_refusal';

    /**
     * @var null|SubjectRequests
     */
    public $refusal = null;

    const KEY_TO_SUBJECT = 'to_subject';
    const KEY_TO_REGULATOR = 'to_regulator';

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return static
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $refusal = key_exists('refusal', $params) ? $params['refusal'] : null;

        if(!($refusal instanceof SubjectRequests)) {
            throw new InvalidArgumentException('refusal must be instance of SubjectRequests');
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Зміна статусу відмови суб'єкта від КП Державною регуляторною службою";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Зміна статусу відмови від КП Державною регуляторною службою';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $format = '<p>Стан розгляду Вашої відмови від комплексного плану змінився. Наразі вона "%s".</p><br>
<p>Підпис та файл Вашої відмови за цим лінком: %s</p>
';
        /* @var $refusalFile GeneratedRefusalFile */
        $refusalFile = $this->refusal->getFiles()->andWhere([BaseFile::tableName().'.type' => GeneratedRefusalFile::TYPE])->one();
        $archive_link = \Yii::$app->urlManager->createAbsoluteUrl(['eds/download-archive', 'file_id' => $refusalFile->id]);

        return sprintf($format, $this->refusal::getStatusLabels()[$this->refusal->status], Html::a($archive_link, $archive_link));
    }

    public function getRoute()
    {
        // TODO: Implement getRoute() method.
    }
}