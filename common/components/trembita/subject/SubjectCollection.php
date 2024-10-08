<?php

namespace common\components\trembita\subject;

use common\components\trembita\subject\services\SearchSubjects;
use common\components\trembita\subject\services\SubjectDetail;
use common\components\trembita\TrembitaCollectionInterface;
use common\components\trembita\TrembitaComponent;
use yii\console\Controller;

/**
 * Class SubjectCollection
 * @package common\components\trembita
 */
class SubjectCollection extends TrembitaComponent implements TrembitaCollectionInterface
{
    public $authorization_token = null;

    public $member_code = null;
    public $subsystem_code = null;

    public $services = [];

    /**
     * @var SearchSubjects
     */
    protected $searchSubjects = null;

    /**
     * @var SubjectDetail
     */
    protected $subjectDetail = null;

    /**
     *
     */
    public function init()
    {
        $this->searchSubjects = new $this->services[0];
        $this->subjectDetail = new $this->services[1];

        parent::init();
    }

    /**
     * @param null $code
     * @param null $passport
     * @param null $name
     * @return Subject|null
     * @throws \SoapFault
     */
    public function get($code = null, $passport = null, $name = null, Controller $console = null)
    {
        /* @var $subject Subject|null */
        $subject = $this->searchSubjects->get($code, $passport, $name, true, $console);

        if($subject) {
            $subject = $this->subjectDetail->get($subject->id, true, $console);
        }

        return $subject;
    }
}