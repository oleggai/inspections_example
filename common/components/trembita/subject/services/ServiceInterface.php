<?php

namespace common\components\trembita\subject\services;

/**
 * Interface ServiceInterface
 * @package common\components\trembita\subject\services
 */
interface ServiceInterface {
    public static function getServiceCode();
    public function getWsdlUrl();
    public function getRequest();
    public function get();
    public static function getSearchFields();
    public function load($result);
}