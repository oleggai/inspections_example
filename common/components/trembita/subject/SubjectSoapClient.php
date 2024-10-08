<?php

namespace common\components\trembita\subject;

use common\components\trembita\subject\services\SearchSubjects;
use common\components\trembita\subject\services\SearchSubjects2;
use common\components\trembita\subject\services\SubjectDetail;
use SoapClient;

class SubjectSoapClient extends SoapClient
{
    /**
     * @var SearchSubjects|SubjectDetail|SearchSubjects2
     */
    public $service = null;

    /**
     * SubjectSoapClient constructor.
     * @param $wsdl
     * @param array|null $options
     */
    public function __construct($wsdl, array $options = null)
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);

        parent::__construct($wsdl, $options);
    }

    /**
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0 )
    {
        $location = $this->service->trembitaComponent->uxp_server_address.':80';

        $request = $this->service->getRequest();

        return parent::__doRequest($request, $location, $action, $version, $one_way);
    }

    /**
     * Функція для генерації ID запиту до UXP сервера
     * @return string
     */
    public function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}