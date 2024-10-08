<?php

namespace common\models\notification\channel;

use common\models\notification\Notification;
use common\models\notification\interfaces\NotifyInterface;

/**
 * Class ScreenChannel
 * @package common\models\notification\channel
 */
class ScreenChannel extends Channel
{
    const TYPE = 'screen';

    /**
     * @param Notification|NotifyInterface $notification
     */
    public function send(Notification $notification)
    {
        $notification->title = $notification->getTitle();
        $notification->description = $notification->getDescription();
        $notification->route = serialize($notification->getRoute());
        $notification->channel = self::TYPE;

        $notification->save();
    }
}