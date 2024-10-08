<?php

namespace common\models\notification;

use common\components\notification\NotificationComponent;
use common\db\ActiveRecord;
use common\models\meta\SingleTableInheritanceQuery;
use common\models\_base\User;
use common\models\notification\channel\ScreenChannel;
use common\models\Subject;
use frontend\models\audit\AuditAppeal;
use thamtech\uuid\helpers\UuidHelper;
use Yii;
use yii\base\InvalidArgumentException;
use yii\behaviors\AttributeBehavior;
use yii\console\Controller;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $title
 * @property string|null $description
 * @property string|null $route
 * @property int|null $read
 * @property int $user_id
 * @property int $subject_id
 * @property string $channel
 * @property int|null $send_to_email
 * @property string $created_at
 * @property string $updated_at
 * @property string $type
 *
 * @property User $user
 * @property Subject $subject
 */
class Notification extends ActiveRecord
{
    const TYPE = 'notification';

    const KEY_TO_SUBJECT = 'to_subject';
    const KEY_TO_REGULATOR = 'to_regulator';

    /**
     * @var null
     */
    public $key = null;

    public $only_channels = [];

    /**
     * @var null
     */
    protected $_user = null;

    /**
     * @var null
     */
    protected $_subject = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'channel'], 'required'],
            [['user_id'], 'required', 'when' => function() {
            return !$this->user_id && !$this->subject_id;
            }, 'message' => 'subject_id and user_id can not be empty'],
            [['description'], 'string'],
            [['uuid'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 200],
            [['route'], 'string', 'max' => 500],
            [['channel'], 'string', 'max' => 100],
            [['type'], 'string', 'max' => 150]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'title' => 'Title',
            'description' => 'Description',
            'route' => 'Route',
            'read' => 'Read',
            'user_id' => 'User ID',
            'channel' => 'Channel',
            'send_to_email' => 'Send To Email',
            'created_at' => 'Created At',
            'type' => 'Type',
        ];
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'note4';
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        if($this->isNewRecord) {
            return $this->_user;
        }
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }

    /**
     * @return null|\yii\db\ActiveQuery
     */
    public function getSubject(Controller $console = null)
    {
        if($this->isNewRecord) {
            if($console) {
                $console->stdout('_subject'.PHP_EOL);
            }
            return $this->_subject;
        }

        if($console) {
            $console->stdout('ActiveQuery'.PHP_EOL);
        }

        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }

    /**
     *
     */
    public function init()
    {
        $this->type = static::TYPE;

        if($this->isNewRecord) {
            $this->uuid = UuidHelper::uuid();
            $this->user_id = $this->user ? $this->user->id : null;

            if(!$this->user_id) {
                $this->subject_id = $this->subject ? $this->subject->id : null;
            }
        }

        parent::init();
    }

    /**
     * @return mixed
     */
    public function getFormattedRoute()
    {
        return unserialize($this->route);
    }

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels Массив каналів, куди шлемо нотіфікейшени. Якщо порожній то на всі канали
     * @return static
     */
    public static function create($key, $params = [], $only_channels)
    {
        $user = key_exists('user', $params) ? $params['user'] : null;
        $subject = key_exists('subject', $params) ? $params['subject'] : null;

        if(!($user instanceof User) && !($subject instanceof Subject)) {
            throw new InvalidArgumentException('User or Subject must be set');
        }

        $params['key'] = $key;
        $params['only_channels'] = $only_channels;

        return new static($params);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function send()
    {
        /* @var $notificationComponent NotificationComponent */
        $notificationComponent = Yii::$app->notificationComponent;
        $notificationComponent->send($this);
    }

    /**
     * Get the notification's delivery channels.
     * @param $channel
     * @return bool
     */
    public function shouldSend($channel)
    {
        /* @var $notificationComponent NotificationComponent */
        $notificationComponent = Yii::$app->notificationComponent;

        // не створюємо нотіфікейшен в систему, оскільки нема юзерів, прив'язаних до суб'єкта
        if(!$this->user_id && $channel->id == ScreenChannel::TYPE) {
            return false;
        }

        // перевіряємо налаштування які проставлені в адмінці
        if(!$notificationComponent->shouldSend(new static(['channel' => $channel->id]))) {
            return false;
        }

        return true;
    }

    /**
     * @return SingleTableInheritanceQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        if (static::TYPE == self::TYPE) {
            return new SingleTableInheritanceQuery(get_called_class(), ['tableName' => self::tableName()]);
        } else {
            return new SingleTableInheritanceQuery(get_called_class(), ['type' => static::TYPE, 'tableName' => self::tableName()]);
        }
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    /**
     * @return array
     */
    public static function getEntities()
    {
        return [
            ApproveComplexPlanNotification::TYPE => new ApproveComplexPlanNotification(),
            CreateComplaintNotification::TYPE => new CreateComplaintNotification(),
            ChangeComplaintStatusNotification::TYPE => new ChangeComplaintStatusNotification(),
            CreateDrsComplaintNotification::TYPE => new CreateDrsComplaintNotification(),
            ChangeDrsComplaintStatusNotification::TYPE => new ChangeDrsComplaintStatusNotification(),
            ChangeDrsRefusalStatusNotification::TYPE => new ChangeDrsRefusalStatusNotification(),
            RefusalComplexPlanDrsNotification::TYPE => new RefusalComplexPlanDrsNotification(),
            RemoveSubjectFromCpNotification::TYPE => new RemoveSubjectFromCpNotification(),
            ChangeComplexInspectionNotification::TYPE => new ChangeComplexInspectionNotification(),
            CreateAuditNotification::TYPE => new CreateAuditNotification(),
            CreateAuditAppealNotification::TYPE => new CreateAuditAppealNotification(),
            ChangeAuditAppealStatusNotification::TYPE => new ChangeAuditAppealStatusNotification(),
            CreateDateProposalNotification::TYPE => new CreateDateProposalNotification(),
            CancelDateProposalNotification::TYPE => new CancelDateProposalNotification(),
            AcceptDateProposalNotification::TYPE => new AcceptDateProposalNotification()
        ];
    }

    /**
     * @param array $row
     * @return ApproveComplexPlanNotification|ChangeComplaintStatusNotification|ChangeDrsComplaintStatusNotification|ChangeDrsRefusalStatusNotification|CreateComplaintNotification|CreateDrsComplaintNotification|RefusalComplexPlanDrsNotification|RemoveSubjectFromCpNotification|static
     * @throws \Exception
     */
    public static function instantiate($row)
    {
        $type = is_array($row) ? $row['type'] : $row;

        $entities = self::getEntities();

        if(array_key_exists($type, $entities)) {
            return $entities[$type];
        } else {
            throw new \Exception('Incorrect type');
        }
    }
}
