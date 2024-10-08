<?php

namespace common\components\trembita;

use common\components\Component;

/**
 * Class TrembitaService
 * @package common\components\trembita
 */
class TrembitaService
{
    /**
     * @var TrembitaComponent|null
     */
    public $trembitaComponent = null;

    /**
     * TrembitaService constructor.
     */
    public function __construct()
    {
        $this->trembitaComponent = Component::getTrembitaComponent();
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        $wsdl_url = $this->trembitaComponent->uxp_server_address . 'wsdl?xRoadInstance=' . $this->trembitaComponent->xroad_instance . "&memberClass=" . $this->trembitaComponent->member_class;

        return $wsdl_url;
    }
}