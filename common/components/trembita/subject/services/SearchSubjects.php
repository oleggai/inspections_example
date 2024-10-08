<?php

namespace common\components\trembita\subject\services;
use common\components\trembita\subject\Subject;
use yii\console\Controller;

/**
 * Class SearchSubjects
 * @package common\components\trembita\subject\services
 */
class SearchSubjects extends SubjectService implements ServiceInterface
{
    /**
     * @var null
     */
    public $code = null;

    /**
     * @var null
     */
    public $passport = null;

    /**
     * @var null
     */
    public $name = null;

    /**
     * @return string
     */
    public static function getServiceCode()
    {
        return 'SearchSubjects';
    }

    /**
     * @return array
     */
    public static function getSearchFields()
    {
        return ['code', 'passport', 'name'];
    }

    /**
     * @param null $code
     * @param null $passport
     * @param null $name
     * @param bool $load
     * @return Subject|mixed|null
     * @throws \SoapFault
     */
    public function get($code = null, $passport = null, $name = null, $load = true, Controller $console = null)
    {
        $this->code = $code;
        $this->passport = $passport;
        $this->name = $name;

        $result = parent::get();

        if($console) {
            $console->stdout('SearchSubjects: '.PHP_EOL);
            $console->stdout(var_dump($result).PHP_EOL);
        }

        $res = json_decode(json_encode($result), true);

        if(!$res['SubjectList']) {
            return null;
        }

        return $load ? $this->load($result) : $result;
    }

    /**
     * @param $result
     * @return Subject
     */
    public function load($result)
    {
        $subject = new Subject();

        $result = json_decode(json_encode($result), true);
        $data = $result['SubjectList']['SubjectInfo'];

        if(isset($data[0])) {

            $key = array_search(\common\models\Subject::STATUS_REGISTERED, array_column($data, 'state_text'));
            if($key !== false) {
                $data = $data[$key];
            } else {
                $data = $data[0];
            }
        }

        $subject->id = $data['id'];
        $subject->full_name = $data['name'];
        $subject->code = $data['code'];
        $subject->status = $data['state_text'];

        return $subject;
    }
}