<?php

namespace common\components\trembita\subject\services;

use common\components\SubjectComponent;
use common\components\trembita\subject\SubjectSoapClient;
use common\components\trembita\TrembitaService;
use common\models\Subject;

/**
 * Class SubjectService
 * @package common\components\trembita\subject\services
 */
class SubjectService extends TrembitaService
{
    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        return parent::getWsdlUrl()."&memberCode=" . $this->trembitaComponent->subject->member_code . "&subsystemCode=" . $this->trembitaComponent->subject->subsystem_code . "&serviceCode=" . static::getServiceCode();
    }

    /**
     * @return mixed
     * @throws \SoapFault
     */
    public function get()
    {
        $wsdl_url = $this->getWsdlUrl();

        try {
            $soapClient = $this->getSoapClient($wsdl_url);

            $soapClient->service = $this;

            $res = $soapClient->{static::getServiceCode()}();

            if($res instanceof \SoapFault) {
                throw $res;
            }

            return $res;

        } catch (\Throwable $fault) {
            throw $fault;
        }
    }

    /**
     * @return array|string|string[]
     * @throws \Exception
     */
    public function getRequest()
    {
        $template = $this->getRequestTemplate();

        $service_code = static::getServiceCode();

        $request = str_replace('{{SERVICE_CODE}}', $service_code, $template);

        $search_fields = static::getSearchFields();

        $tag = '';
        $value = '';

        foreach ($search_fields as $search_field) {
            if($this->{$search_field}) {

                $tag = $search_field;
                $value = $this->{$search_field};

                if($search_field == 'code') {
                    $subject_type = SubjectComponent::getType($this->{$search_field});
                    if(in_array($subject_type, [Subject::TYPE_FIZ_WITHOUT_CODE, Subject::TYPE_FIZ_ID_KARTKA])) {
                        $tag = 'passport';
                    }
                }

                break;
            }
        }

        $body = <<<BODY
 <soapenv:Body>
      <edr:$service_code>
         <edr:$tag>$value</edr:$tag>
      </edr:$service_code>
   </soapenv:Body>
BODY;

        $request = str_replace('{{BODY}}', $body, $request);

        return $request;
    }

    /**
     * @param $wsdl_url
     * @return SubjectSoapClient
     */
    protected function getSoapClient($wsdl_url)
    {
        // бібліотека phpspreadsheet має баг https://github.com/PHPOffice/PHPExcel/issues/1221. Щоб пофіксити його або треба оновити бібліотеку або дописати цю строку перед створенням SoapClient
        libxml_disable_entity_loader(false);

        return new SubjectSoapClient(
            $wsdl_url,
            [
                'trace' => 0,
                'exceptions' => 0,
                'encoding' => 'UTF-8',
                'soap_version' => SOAP_1_1,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ],
                ]),
            ]);
    }

    /**
     * @return string
     */
    protected function getRequestTemplate()
    {
        $request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:edr="http://nais.gov.ua/api/sevdeir/EDR" xmlns:xro="http://x-road.eu/xsd/xroad.xsd" xmlns:iden="http://x-road.eu/xsd/identifiers">
   <soapenv:Header>
      <edr:AuthorizationToken>'.$this->trembitaComponent->subject->authorization_token.'</edr:AuthorizationToken>
      <xro:client iden:objectType="SUBSYSTEM">
         <iden:xRoadInstance>'.$this->trembitaComponent->xroad_instance.'</iden:xRoadInstance>
         <iden:memberClass>'.$this->trembitaComponent->member_class.'</iden:memberClass>
         <iden:memberCode>'.$this->trembitaComponent->member_code.'</iden:memberCode>
         <!--Optional:-->
         <iden:subsystemCode>'.$this->trembitaComponent->subsystem_code.'</iden:subsystemCode>
      </xro:client>
      <xro:service iden:objectType="SERVICE">
         <iden:xRoadInstance>'.$this->trembitaComponent->xroad_instance.'</iden:xRoadInstance>
         <iden:memberClass>'.$this->trembitaComponent->member_class.'</iden:memberClass>
         <iden:memberCode>'.$this->trembitaComponent->subject->member_code.'</iden:memberCode>
         <!--Optional:-->
         <iden:subsystemCode>'.$this->trembitaComponent->subject->subsystem_code.'</iden:subsystemCode>
         <iden:serviceCode>{{SERVICE_CODE}}</iden:serviceCode>
         <!--Optional:-->
     
      </xro:service>
      <xro:userId>'.$this->trembitaComponent->user_id_code.'</xro:userId>
      <xro:id></xro:id>
      <xro:protocolVersion>4.0</xro:protocolVersion>
   </soapenv:Header>
  {{BODY}}
</soapenv:Envelope>';

        return $request;
    }
}