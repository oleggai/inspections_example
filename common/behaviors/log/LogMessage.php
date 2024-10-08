<?php

namespace common\behaviors\log;

use lav45\activityLogger\LogMessageDTO;

/**
 * Class LogMessage
 * @package common\behaviors\log
 */
class LogMessage extends LogMessageDTO
{
    public $sphere_id = null;
    public $regulator_id = null;
}