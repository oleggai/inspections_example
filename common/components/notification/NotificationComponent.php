<?php


namespace common\components\notification;

use common\helpers\ArrayHelper;
use common\models\notification\channel\EmailChannel;
use common\models\notification\channel\ScreenChannel;
use common\models\notification\Notification;
use common\models\notification\NotificationSettings;
use common\models\User;
use yii\base\Component;

/**
 * Class NotificationComponent
 * @package common\components
 */
class NotificationComponent extends Component
{
    public $channels = [];

    public $meta = [];

    protected $_notification_settings = null;

    /**
     * @return NotificationSettings[]|null
     */
    public function getNotificationSettings()
    {
        if ($this->_notification_settings === null) {
            $notificationSettings = NotificationSettings::find()->all();
            foreach ($notificationSettings as $notificationSetting) {
                $this->_notification_settings[$notificationSetting->type][$notificationSetting->channel] = $notificationSetting;
            }
        }

        return $this->_notification_settings;
    }

    /**
     * Перевіряє налаштування, які проставляються в адмінці та
     * в залежності від цих налаштувань, відправляє повідомлення по каналу чи ні
     * @param Notification $notification
     * @return bool|int
     */
    public function shouldSend(Notification $notification)
    {
        $notification_settings = $this->getNotificationSettings();

        /* @var $notificationSetting NotificationSettings */
        $notificationSetting = isset($notification_settings[$notification->type][$notification->channel]) ? $notification_settings[$notification->type][$notification->channel] : null;

        return $notificationSetting ? $notificationSetting->should_send : true;
    }

    /**
     * Send a notification to all channels
     * @param Notification $notification
     * @param array|null $channels
     * @throws \yii\base\InvalidConfigException
     */
    public function send(Notification $notification, array $channels = null)
    {
        if ($channels === null) {
            $channels = array_keys($this->channels);
        }

        foreach ((array)$channels as $id) {

            if ($notification->only_channels && !in_array($id, $notification->only_channels)) {
                continue;
            }

            $channel = $this->getChannel($id);
            if (!$notification->shouldSend($channel)) {
                continue;
            }

            $handle = 'to' . ucfirst($id);
            try {
                if ($notification->hasMethod($handle)) {
                    call_user_func([clone $notification, $handle], $channel);
                } else {
                    $channel->send(clone $notification);
                }
            } catch (\Exception $e) {
                \Yii::warning("Notification sended by channel '$id' has failed: " . $e->getMessage(), __METHOD__);
                throw $e;
            }
        }
    }

    /**
     * Gets the channel instance
     * @param $id
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getChannel($id)
    {
        if (!isset($this->channels[$id])) {
            throw new \InvalidArgumentException("Unknown channel '{$id}'.");
        }

        if (!is_object($this->channels[$id])) {
            $this->channels[$id] = $this->createChannel($id, $this->channels[$id]);
        }

        return $this->channels[$id];
    }

    /**
     * @param $id
     * @param $config
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    protected function createChannel($id, $config)
    {
        return \Yii::createObject($config, [$id]);
    }

    /**
     * @param User $user
     * @return int|string
     */
    public static function getCountUnread(User $user = null)
    {
        /* @var $user User */
        $user = $user ?: \Yii::$app->user->identity;

        return Notification::find()
            ->where(['user_id' => $user->id, 'channel' => ScreenChannel::TYPE])
            ->andWhere(['not', ['read' => true]])
            ->count();
    }

    /**
     * @param User|null $user
     * @param int $limit
     * @return array|Notification[]|\yii\db\ActiveRecord[]
     */
    public static function getLimitUnread(User $user = null, $limit = 10)
    {
        /* @var $user User */
        $user = $user ?: \Yii::$app->user->identity;

        return Notification::find()
            ->where(['user_id' => $user->id, 'channel' => ScreenChannel::TYPE])
            ->andWhere(['not', ['read' => true]])
            ->limit($limit)
            ->orderBy('created_at DESC')
            ->all();
    }

    /**
     * @return string
     */
    public static function detectClassForIcon()
    {
        $red_class = 'urgent_note'; // for urgent notifications
        $green_class = 'has_note';

        return $green_class;
    }

    /**
     * @return array|Notification|null|\yii\db\ActiveRecord
     */
    public static function isUnread()
    {
        /* @var $currentUser User */
        $currentUser = \Yii::$app->user->identity;

        return Notification::find()
            ->where(['user_id' => $currentUser->id, 'channel' => ScreenChannel::TYPE])
            ->andWhere(['not', ['read' => true]])->limit(1)->one();
    }

    /**
     * @param Notification|null $notification
     */
    public static function markAsRead(Notification $notification = null)
    {
        $query = Notification::find()
            ->where(['user_id' => \Yii::$app->user->id, 'read' => [null, false]])
            ->andWhere(['not', ['channel' => EmailChannel::TYPE]]);

        if ($notification) {
            $query->andWhere(['id' => $notification->id]);
        }

        Notification::updateAll(['read' => true], ['id' => ArrayHelper::getColumn($query->all(), 'id')]);
    }
}