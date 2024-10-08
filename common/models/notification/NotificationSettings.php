<?php

namespace common\models\notification;

use common\db\ActiveRecord;

/**
 * This is the model class for table "notification_settings".
 *
 * @property int $id
 * @property string $type
 * @property string $channel
 * @property int $should_send
 */
class NotificationSettings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'channel'], 'required'],
            [['type'], 'string', 'max' => 150],
            [['channel'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'channel' => 'Channel',
            'should_send' => 'Should Send',
        ];
    }
}
