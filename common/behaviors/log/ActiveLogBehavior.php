<?php

namespace common\behaviors\log;

use common\models\ActivityLog;
use common\models\FSubjectObjects;
use common\models\FSubjectRegulator;
use common\models\functionality_log\FunctionalityLog;
use yii\helpers\ArrayHelper;

/**
 * Class ActiveLogBehavior
 * @package common\behaviors
 *
 * @property $getEntityName
 * @property $attributes
 *
 * Логирует изменения атрибутов при updateAttributes();
 */
class ActiveLogBehavior extends \lav45\activityLogger\ActiveLogBehavior
{

    /**
     * @var bool
     */
    public $softDelete = true;

    public $functionality_logs = [];

    public $actionLabels = [
        'create' => 'created',
        'update' => 'updated',
        'delete' => 'removed',
    ];

    /**
     * @var array [
     *  'title' => [
     *      'new' => ['value' => 'New title'],
     *  ],
     *  'is_publish' => [
     *      'old' => ['value' => false],
     *      'new' => ['value' => true],
     *  ],
     *  'status' => [
     *      'old' => ['id' => 0, 'value' => 'Disabled'],
     *      'new' => ['id' => 1, 'value' => 'Active'],
     *  ],
     *  'owner_id' => [
     *      'old' => ['id' => 1, 'value' => 'admin'],
     *      'new' => ['id' => 2, 'value' => 'lucy'],
     *  ]
     * ]
     */
    private $changedAttributes = [];
    /**
     * @var string
     */
    private $actionName;

    /**
     * @param $attributes
     * @param array $wrong_risk
     * @return int|void
     */
    public function updateAttributesWithLog($attributes, $wrong_risk = null, $markedFSubject = null)
    {
        $this->changedAttributes = $this->prepareChangedAttributes();
        $this->actionName = 'updated';

        if (empty($this->changedAttributes)) {
            return;
        }

        $owner = $this->owner;
        $sphere_id = isset($owner->sphere->id) ? $owner->sphere->id : null;
        $regulator_id = isset($owner->regulator->id) ? $owner->regulator->id : null;

        $abbreviated1 = '';
        if($owner instanceof FSubjectRegulator) {
            $abbreviated1 = 'fsr';
        } elseif ($owner instanceof FSubjectObjects) {
            $abbreviated1 = 'fso';
        }

        $abbreviated2 = '';
        if($markedFSubject instanceof FSubjectRegulator) {
            $abbreviated2 = 'fsr';
        } elseif ($markedFSubject instanceof FSubjectObjects) {
            $abbreviated2 = 'fso';
        }

        $is_fso_change = $wrong_risk && $abbreviated1 == 'fso' && $abbreviated1 == $abbreviated2;

        if($wrong_risk) {

            $wrong_risk->data[$abbreviated1.'_'.$owner->id.'_'.$abbreviated2.'_'.$markedFSubject->id] = $this->changedAttributes;

            if(!$wrong_risk->save) {
                $wrong_risk->notify();
            }
        }

        if(!$wrong_risk || ($wrong_risk && $wrong_risk->save) || $is_fso_change) {

            $logMessage = new LogMessage();
            $logMessage->sphere_id = $sphere_id;
            $logMessage->regulator_id = $regulator_id;
            $logMessage->entityName = $this->getEntityName();
            $logMessage->entityId = $this->getEntityId();
            $logMessage->action = $this->actionName;
            $logMessage->data = $this->changedAttributes;

            /* @var $activityLog ActivityLog */
            $activityLog = $this->getLogger()->log($logMessage);

            $this->saveFunctionalityLog($activityLog);

            return $this->owner->updateAttributes($attributes);
        }
    }

    /**
     * @param string $label
     * @return string|null
     */
    private function getActionLabel($label)
    {
        return ArrayHelper::getValue($this->actionLabels, $label);
    }

    /**
     *
     */
    public function beforeDelete()
    {
        if ($this->softDelete === false) {
            $this->getLogger()->delete(new LogMessage([
                'entityName' => $this->getEntityName(),
                'entityId' => $this->getEntityId(),
            ]));
        }

        $owner = $this->owner;

        if($owner->getBehavior('softDeleteBehavior')) {
            $this->saveMessage($this->getActionLabel('delete'), $this->prepareChangedAttributes());
        } else {
            $this->saveMessage($this->getActionLabel('delete'), $this->prepareChangedAttributes(true));
        }
    }

    /**
     * @param bool $unset
     * @return array
     */
    protected function prepareChangedAttributes($unset = false)
    {
        $result = [];
        foreach ($this->attributes as $attribute => $options) {
            $old = $this->owner->getOldAttribute($attribute);
            $new = $unset === false ? $this->owner->getAttribute($attribute) : null;

            if ($this->isEmpty($old) && $this->isEmpty($new)) {
                continue;
            }
            if ($unset === false && $this->isAttributeChanged($attribute) === false) {
                continue;
            }

            $result[$attribute] = $this->resolveStoreValues($old, $new, $options);
        }
        return $result;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    protected function isAttributeChanged($attribute)
    {
        return $this->owner->isAttributeChanged($attribute, $this->identicalAttributes);
    }

    /**
     * @param string $action
     * @param array $data
     */
    protected function saveMessage($action, array $data)
    {
        $data = $this->beforeSaveMessage($data);

        $owner = $this->owner;
        $sphere_id = isset($owner->sphere->id) ? $owner->sphere->id : null;
        $regulator_id = isset($owner->regulator->id) ? $owner->regulator->id : null;

        $logMessage = new LogMessage();
        $logMessage->sphere_id = $sphere_id;
        $logMessage->regulator_id = $regulator_id;
        $logMessage->entityName = $this->getEntityName();
        $logMessage->entityId = $this->getEntityId();
        $logMessage->action = $action;
        $logMessage->data = $data;

        /* @var $activityLog ActivityLog */
        $activityLog = $this->getLogger()->log($logMessage);

        $this->saveFunctionalityLog($activityLog);

        $this->afterSaveMessage();
    }

    /**
     * @param ActivityLog|null $activityLog
     * @return null
     */
    protected function saveFunctionalityLog(ActivityLog $activityLog = null)
    {
        if(!$activityLog) {
            return null;
        }

        foreach ($this->functionality_logs as $functionality_log) {
            /* @var $functionalityLogEntity FunctionalityLog */
            $functionalityLogEntity = new $functionality_log['entity'];
            $entity_id = $functionalityLogEntity::ENTITY_ID_NAME;
            $functionalityLogEntity->{$entity_id} = $functionality_log['get_entity_id']();
            $functionalityLogEntity->activity_log_id = $activityLog->id;

            $functionalityLogEntity->save();
        }
    }
}