<?php

namespace common\models\version;

/**
 * Class ObjectVersion
 * @package common\models\version
 *
 * @property int $entity_object_id
 */
class ObjectVersion extends EntityVersion
{
    const TYPE = 'object';
}