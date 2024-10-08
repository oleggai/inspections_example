<?php

namespace common\models\notification;

use common\helpers\Html;
use common\models\ComplexInspection;
use common\models\notification\interfaces\NotifyInterface;
use common\models\PlanInspection;
use yii\base\InvalidArgumentException;

/**
 * Class ChangeComplexInspectionNotification Сповіщення КО про зміни в перевірках, що входять в КП
 * @package common\models\notification
 */
class ChangeComplexInspectionNotification extends Notification implements NotifyInterface
{
    const TYPE = 'change_complex_inspection';

    /**
     * @var PlanInspection[]
     */
    public $changed_complex_inspections = [];

    /**
     * @param $key
     * @param array $params
     * @param array $only_channels
     * @return Notification
     */
    public static function create($key, $params = [], $only_channels = [])
    {
        $changed_complex_inspections = key_exists('changed_complex_inspections', $params) ? $params['changed_complex_inspections'] : null;

        foreach ($changed_complex_inspections as $complex_inspection) {
            if(!($complex_inspection instanceof ComplexInspection)) {
                throw new InvalidArgumentException('item in array must be instance of ComplexInspection');
            }
        }

        return parent::create($key, $params, $only_channels);
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return "Сповіщення КО про зміни в комплексних перевірках";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Список змінених перевірок в комплексному плані Державною регуляторною службою';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $styleTable = "style='width: 100%; border-collapse: collapse; border: 1px solid black;'";
        $styleTh = "style='border: 1px solid black; padding: 15px; text-align: left;'";
        $styleTd = $styleTh;
        $table = "<table $styleTable>
<thead>
<tr>
<th $styleTh>Назва</th>
<th $styleTh>Адреса</th>
<th $styleTh>Код</th>
</tr>
</thead>
<tbody>";

        foreach ($this->changed_complex_inspections as $complex_inspection) {

            $subjectUrl = \Yii::$app->urlManager->createAbsoluteUrl(['subject/view', 'id' => $complex_inspection->subject_id]);
            $subjectLink = Html::a($complex_inspection->name, $subjectUrl);

            $table .= "<tr>
<td $styleTd>$subjectLink</td>
<td $styleTd>$complex_inspection->address</td>
<td $styleTd>$complex_inspection->code</td>
</tr>";

        }
        $table .= '</tbody>
</table>';

        $format = '%s';

        return sprintf($format, $table);
    }


    public function getRoute()
    {
        // TODO: Implement getRoute() method.
    }
}