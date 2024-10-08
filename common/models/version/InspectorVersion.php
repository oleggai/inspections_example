<?php

namespace common\models\version;
use common\behaviors\log\ActiveLogBehavior;
use common\behaviors\VersionBehavior;
use common\models\_base\InspectionReason;
use common\models\ActivityLog;
use common\models\AnnualInspection;
use common\models\functionality_log\entities\InspectionLog;
use common\models\InspectionInspectorMark;
use common\models\Inspector;
use common\models\junction\InspectionInspectorVersion;
use common\models\Regulator;
use yii\db\ActiveQuery;

/**
 * Class InspectorVersion
 * @package common\models\version
 *
 * @property string $pib
 * @property string $position
 * @property string $certificate_number
 * @property integer $regulator_id
 * @property Regulator $regulator
 * @property int $entity_inspector_id
 * @property Inspector $currentInspector
 * @property AnnualInspection[] $inspections
 * @property InspectionReason[] $inspectionReasons
 * @property InspectionInspectorMark[] $marks
 */
class InspectorVersion extends EntityVersion
{
    const TYPE = 'inspector';

    /**
     * @var null Must be set for logging
     */
    public $inspection_id = null;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pib', 'position', 'regulator_id', 'inspection_id'], 'safe'],
            [['pib', 'position'], 'trim'],
            ['certificate_number', 'string', 'max' => 60],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => VersionBehavior::class,
                'uniqueAttributes' => ['pib', 'position', 'regulator_id', 'entity_inspector_id']
            ],
            [
                'class' => ActiveLogBehavior::class,
                'getEntityName' => function() {
                    return ActivityLog::ENTITY_INSPECTOR_VERSION;
                },
                'attributes' => ['pib', 'position', 'regulator_id', 'certificate_number'],
                'functionality_logs' => [
                    [
                        'entity' => InspectionLog::class,
                        'get_entity_id' => function () {
                            return $this->inspection_id;
                        }
                    ],
                ]
            ],
        ]);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'pib' => 'ПІБ',
            'position' => 'Посада',
            'regulator_id' => 'Орган',
            'certificate_number' => 'Номер посвідчення інспектора праці, що брав участь у відвідуванні'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegulator()
    {
        return $this->hasOne(Regulator::class, ['id' => 'regulator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentInspector()
    {
        return $this->hasOne(Inspector::class, ['id' => 'entity_inspector_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getInspections()
    {
        return $this->hasMany(AnnualInspection::class, ['id' => 'inspection_id'])
            ->viaTable('inspection_entity_version', ['entity_version_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspectionReasons()
    {
        return $this->hasMany(InspectionReason::class, ['id' => 'reason_id'])
            ->viaTable('reason_entity_version', ['entity_version_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMarks()
    {
        return $this->hasMany(InspectionInspectorMark::class, ['inspection_entity_version_id' => 'id'])
            ->viaTable('inspection_entity_version', ['entity_version_id' => 'id']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->pib;
    }
}