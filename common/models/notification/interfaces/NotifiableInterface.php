<?php

namespace common\models\notification\interfaces;

/**
 * Interface NotifiableInterface
 * @package common\models\notification\interfaces
 */
interface NotifiableInterface
{
    public function notify($changedAttributes = [], $additional_info = []);
}