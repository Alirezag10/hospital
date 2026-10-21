<?php
namespace app\controllers;

use app\components\Access;
use app\components\RoleController;
use app\models\AdmissionService;
use app\models\Prescription;
use app\models\RecordChange;
use app\models\Service;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class RecordChangeController extends RoleController
{
    public function actionIndex($type, $id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $classes = [
            'prescription' => Prescription::class,
            'admission_service' => AdmissionService::class,
        ];
        $class = is_string($type) ? ($classes[$type] ?? null) : null;
        $record = $id === false || $class === null ? null : $class::findOne($id);
        if ($record === null) {
            throw new NotFoundHttpException('رکورد پیدا نشد.');
        }

        $admission = Access::admission($record->admission_id);
        $changes = RecordChange::find()
            ->with('actor')
            ->where(['entity_type' => $type, 'entity_id' => $id])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $serviceIds = [];

        if ($type === 'admission_service') {
            foreach ($changes as $change) {
                foreach ([$change->before_data, $change->after_data] as $json) {
                    $data = json_decode($json, true);
                    if (isset($data['service_id'])) {
                        $serviceIds[] = (int) $data['service_id'];
                    }
                }
            }
        }

        $services = $serviceIds
            ? Service::find()->where(['id' => array_unique($serviceIds)])->all()
            : [];
        $serviceNames = ArrayHelper::map($services, 'id', 'title');

        return $this->render('index', compact('admission', 'changes', 'type', 'id', 'serviceNames'));
    }
}
