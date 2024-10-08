<?php

namespace common\behaviors\log;

use common\helpers\ArrayHelper;
use common\models\ActivityLog;
use lav45\activityLogger\LogMessageDTO;

/**
 * Class DbStorage
 * @package common\behaviors\log
 */
class DbStorage extends \lav45\activityLogger\DbStorage
{
    /**
     * @param LogMessageDTO|\common\behaviors\log\LogMessage $message
     * @return ActivityLog|void
     * @throws \Exception
     */
    public function save(LogMessageDTO $message)
    {
        $options = array_filter([
            'entity_name' => $message->entityName,
            'entity_id' => $message->entityId,
            'created_at' => $message->createdAt,
            'user_id' => $message->userId,
            'user_name' => $message->userName,
            'action' => $message->action,
            'env' => $message->env,
            'data' => $this->encode($message->data),
            'sphere_id' => $message->sphere_id,
            'regulator_id' => $message->regulator_id
        ]);

        $activityLog = new ActivityLog();
        $activityLog->attributes = $options;

        $requestedRoute = \Yii::$app->requestedRoute;
        $requestedParams = implode(' ', ArrayHelper::clearEmpty(\Yii::$app->requestedParams));

        if($requestedParams) {
            $requestedParams = ' '.$requestedParams;
        }

        $activityLog->url = $requestedRoute.$requestedParams;

        if($activityLog->save()) {
            return $activityLog;
        } else {
            throw new \Exception('ActivityLog entity is not saved');
        }
    }

    /**
     * @param array|string $data
     * @return string
     */
    private function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}