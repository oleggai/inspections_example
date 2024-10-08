<?php

namespace common\behaviors\log;

use common\components\Component;
use common\models\ActivityLog;
use lav45\activityLogger\LogMessageDTO;
use lav45\activityLogger\StorageInterface;
use yii\di\Instance;

/**
 * Class Manager
 * @package common\behaviors\log
 */
class Manager extends \lav45\activityLogger\Manager
{

    public $messageClass = [
        'class' => LogMessage::class
    ];

    /**
     * @return StorageInterface|object|string
     * @throws \yii\base\InvalidConfigException
     */
    protected function getStorage()
    {
        if (!$this->storage instanceof StorageInterface) {
            $this->storage = Instance::ensure($this->storage, StorageInterface::class);
        }
        return $this->storage;
    }

    /**
     * @param array $options
     * @return bool|ActivityLog
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    private function saveMessage(array $options)
    {
        if ($this->enabled === false) {
            return false;
        }
        $options = array_filter($options);
        if (empty($options)) {
            return false;
        }

        /** @var LogMessage $message */
        $message = \Yii::createObject(array_merge(
            $this->messageClass,
            $this->getUserOptions(),
            ['createdAt' => time()],
            $options
        ));

        try {
            /* @var $activityLog ActivityLog */
            $activityLog = $this->getStorage()->save($message);

        } catch (\Exception $e) {
            return $this->throwException($e);
        } catch (\Throwable $e) {
            return $this->throwException($e);
        }

        return $activityLog;
    }

    /**
     * @return array
     */
    protected function getUserOptions()
    {
        $userComponent = Component::getUserComponent();

        if ($user = \Yii::$app->get($this->user, false)) {
            /** @var \yii\web\IdentityInterface $identity */
            $identity = $user->identity;
            return $identity ? [
                'userId' => $identity->getId(),
                'userName' => $identity->{$this->userNameAttribute}
            ] : [];
        } else {

            return [
                'userId' => $userComponent->user_id,
                'userName' => null
            ];
        }
    }

    /**
     * @param LogMessageDTO $message
     * @return bool|null
     * @throws \Exception
     * @throws \Throwable
     */
    public function log(LogMessageDTO $message)
    {
        if (false === $this->enabled) {
            return false;
        }

        $message->createdAt = time();

        $userOptions = $this->getUserOptions();

        if ($userOptions) {
            $message->userId = $userOptions['userId'];
            $message->userName = $userOptions['userName'];
        }

        $activityLog = null;
        try {

            $activityLog = $this->getStorage()->save($message);

        } catch (\Exception $e) {
            $this->throwException($e);
        } catch (\Throwable $e) {
            $this->throwException($e);
        }
        return $activityLog;
    }

    /**
     * @param \Exception|\Throwable $e
     * @throws \Exception|\Throwable
     * @return bool
     */
    private function throwException($e)
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($this->debug) {
            throw $e;
        }
        \Yii::warning($e->getMessage());
        return false;
    }
}