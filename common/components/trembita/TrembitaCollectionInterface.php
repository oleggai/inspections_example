<?php

namespace common\components\trembita;

/**
 * Interface TrembitaCollectionInterface
 * @package common\components\trembita
 */
interface TrembitaCollectionInterface {
    public function getWsdlUrl();
    public function get();
}