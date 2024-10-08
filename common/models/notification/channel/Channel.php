<?php

namespace common\models\notification\channel;

use common\models\notification\Notification;

/**
 * Class Channel
 * @package common\models\notification\chanel
 */
abstract class Channel extends \yii\base\BaseObject
{
    public $id;

    public function __construct($id, $config = [])
    {
        $this->id = $id;
        parent::__construct($config);
    }

    public abstract function send(Notification $notification);
}