<?php

namespace common\components\trembita;

use common\components\trembita\subject\SubjectCollection;
use yii\base\Component;

/**
 * Class TrembitaComponent
 * @package common\components\trembita
 *
 * @property SubjectCollection $subject
 */
class TrembitaComponent extends Component
{
    /**
     * @var null
     */
    public $uxp_server_address = null;

    /**
     * @var null
     */
    public $xroad_instance = null;

    /**
     * @var null
     */
    public $member_class = null;

    /**
     * @var string
     */
    public $member_code = '';

    /**
     * @var null
     */
    public $subsystem_code = null;

    /**
     * @var null
     */
    public $user_id_code = null;

    /**
     * @var array
     */
    public $collections = [];

    /**
     * @param string $name
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if(array_key_exists($name, $this->collections)) {
            return $this->getCollection($name);
        }

        return parent::__get($name);
    }

    /**
     * @param $id
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getCollection($id)
    {
        if(!isset($this->collections[$id])){
            throw new \InvalidArgumentException("Unknown collection '{$id}'.");
        }

        if (!is_object($this->collections[$id])) {
            $this->collections[$id] = $this->createCollection($id, $this->collections[$id]);
        }

        return $this->collections[$id];
    }

    /**
     * @param $id
     * @param $config
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    protected function createCollection($id, $config)
    {
        return \Yii::createObject($config);
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        $wsdl_url = 'https://' . $this->uxp_server_address . '/wsdl?xRoadInstance=' . $this->xroad_instance . "&memberClass=" . $this->member_class;

        return $wsdl_url;
    }
}