<?php

namespace common\components\trembita\subject\services;
use common\components\trembita\subject\Subject;
use yii\console\Controller;

/**
 * Class SubjectDetail
 * @package common\components\trembita\subject\services
 */
class SubjectDetail extends SubjectService implements ServiceInterface
{
    /**
     * @var null
     */
    public $id = null;

    /**
     * @return string
     */
    public static function getServiceCode()
    {
        return 'SubjectDetail';
    }

    /**
     * @return array
     */
    public static function getSearchFields()
    {
        return ['id'];
    }

    /**
     * @param null $id
     * @return mixed
     * @throws \SoapFault
     */
    public function get($id = null, $load = true, Controller $console = null)
    {
        $this->id = $id;

        $result = parent::get();

        if($console) {
            $console->stdout('SubjectDetail: '.PHP_EOL);
            $console->stdout(var_dump($result).PHP_EOL);
        }

        $res = json_decode(json_encode($result), true);

        if(array_key_exists('errors', $res) && $res['errors']) {
            return null;
        }

        return $load ? $this->load($result, $console) : $result;
    }

    /**
     * @param $result
     * @return Subject
     */
    public function load($result, Controller $console = null)
    {
        $subject = new Subject();

        $result = json_decode(json_encode($result), true);
        $data = $result['Subject'];

        if($console) {
            $console->stdout(var_dump($data['branches']).PHP_EOL);
        }

        $subject->id = $data['id'];
        $subject->code = $data['code'] ?? null;

        $full_name = $subject->full_name;
        if(!$full_name) {
            $full_name = $data['names']['display'] ?? null;
        }

        $subject->full_name = $full_name;
        $subject->short_name = $data['names']['short'] ?? null;
        $subject->status = $data['state_text'] ?? null;

        $subject->location = $data['address']['address'];

        $head = [];
        if(isset($data['heads']['head'][0])) {

            $heads = $data['heads']['head'];

            foreach ($heads as $item) {
                if($item['role'] == 3) {
                    $head = $item;
                }
            }

        } else {

            if(isset($data['heads']['head']['last_name'])) {
                $head = $data['heads']['head'];
            }
        }

        if(isset($head['last_name'])) {
            $subject->ceo_name = $head['last_name'].' '.$head['first_middle_name'];
        }

        $subject->email = $data['contacts']['email'] ?? null;

        $branches = [];
        if(isset($data['branches']['branch'][0])) {
            $branches = $data['branches']['branch'];
        } else {
            if(isset($data['branches']['branch']['name'])) {
                $branches[] = $data['branches']['branch'];
            }
        }

        $subject->branches = $branches;

        $subject->activity_kinds = $data['activity_kinds']['activity_kind'] ?? [];

        return $subject;
    }
}