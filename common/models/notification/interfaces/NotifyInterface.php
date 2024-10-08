<?php

namespace common\models\notification\interfaces;

/**
 * Interface NotifyInterface
 * @package common\models\notification
 */
interface NotifyInterface
{
    public function getTitle();
    public function getDescription();
    public function getRoute();

    /**
     * використовується для ініціалізації налаштувань повідомлень в адмінці
     * @return mixed
     */
    public function getLabel();
}