<?php

namespace common\components\trembita\subject;

use yii\base\Model;

/**
 * Class Subject
 * @package common\components\trembita\subject
 */
class Subject extends Model
{
    public $id = null;

    public $code = null;

    public $full_name = null;

    public $short_name = null;

    public $location = null;

    public $status = null;

    public $ceo_name = null;

    public $email = null;

    public $branches = [];

    public $activity_kinds = [];
}