<?php

namespace common\models\version;

use common\models\SubjectPerson;
use yii\behaviors\AttributeBehavior;
use common\db\ActiveRecord;
use common\models\meta\SingleTableInheritanceQuery;

/**
 * Class EntityVersion
 * @package common\models\version
 *
 * @property integer $id
 * @property string $created_at
 * @property string $type
 */
class EntityVersion extends ActiveRecord
{
    const TYPE = 'entity';

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%entity_version}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => function ($event) {
                    return (new \DateTime())->format('Y-m-d H:i:s');
                }
            ],
        ];
    }

    /**
     *
     */
    public function init()
    {
        $this->type = static::TYPE;
        parent::init();
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
        $this->type = static::TYPE;

        if (!parent::beforeSave($insert)) {
            return false;
        }

        // here is code

        return true;
    }

    /**
     * @param array $row
     * @return InspectorVersion|ObjectVersion|static
     * @throws \Exception
     */
    public static function instantiate($row)
    {
        $type = is_array($row) ? $row['type'] : $row;

        switch ($type) {
            case InspectorVersion::TYPE:
                return new InspectorVersion();
            case ObjectVersion::TYPE:
                return new ObjectVersion();
            case SubjectPerson::TYPE:
                return new SubjectPerson();
            default:
                throw new \Exception('Incorrect type');
        }
    }
}