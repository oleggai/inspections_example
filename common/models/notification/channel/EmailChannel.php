<?php

namespace common\models\notification\channel;
use common\helpers\ArrayHelper;
use common\helpers\DateHelper;
use common\helpers\StringHelper;
use common\helpers\UrlHelper;
use common\models\notification\Notification;
use yii\di\Instance;

/**
 * Class EmailChannel
 * @package common\models\notification\channel
 */
class EmailChannel extends Channel
{
    const TYPE = 'email';

    /**
     * @var array the configuration array for creating a [[\yii\mail\MessageInterface|message]] object.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     */
    public $message = [];

    /**
     * @var \yii\mail\MailerInterface|array|string the mailer object or the application component ID of the mailer object.
     * After the EmailChannel object is created, if you want to change this property, you should only assign it
     * with a mailer object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $mailer = 'mailer';

    /**
     * @return mixed
     */
    public static function getTestEmail()
    {
        return \Yii::$app->params['supportEmail'];
    }


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->mailer = Instance::ensure($this->mailer, 'yii\mail\MailerInterface');
    }

    /**
     * @param Notification $notification
     */
    public function send(Notification $notification)
    {
        $notification->title = $notification->getTitle();
        $notification->description = $notification->getDescription();
        $notification->route = serialize($notification->getRoute());
        $notification->channel = self::TYPE;

        $notification->save();

        if(DateHelper::isSuitableEmailTime()) {
            $this->doSend($notification);
        }
    }

    /**
     * @param Notification $notification
     * @return bool
     */
    public function doSend(Notification $notification)
    {
        $message = $this->composeMessage($notification);

        if(!$message || ($message && $message->send())) {
            $notification->send_to_email = true;

            $notification->updateAttributes(['send_to_email']);

            return true;
        }
        return false;
    }

    /**
     * @param Notification $notification
     * @return array
     */
    protected function detectTo(Notification $notification)
    {
        $to = [];
        if(!UrlHelper::isProd()) {
            $to = array_key_exists('localEmail', \Yii::$app->params) ? [\Yii::$app->params['localEmail']] : [self::getTestEmail()];
        } else {

            $user_email = null;
            $subject_email = null;

            if($notification->user_id) {
                $user_email = StringHelper::trim($notification->user->email);
            }

            if($notification->subject_id) {
                $subject_email = StringHelper::trim($notification->subject->email);
            }

            $to[] = $user_email;
            $to[] = $subject_email;
        }

        return array_unique(ArrayHelper::clearEmpty($to));
    }

    /**
     * Composes a mail message with the given body content.
     * @param Notification $notification
     * @return \yii\mail\MessageInterface
     */
    protected function composeMessage(Notification $notification)
    {
        $to = $this->detectTo($notification);

        if(!$to) {
            return null;
        }

        $message = $this->mailer->compose();
        $message->setTo($to);
        $message->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot']);
        $message->setSubject($notification->title);
        $message->setHtmlBody($notification->description);

        return $message;
    }
}