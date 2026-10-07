<?php
namespace app\controllers;

use app\models\Admission;
use app\models\AdmissionSearch;
use app\models\Patient;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AdmissionController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new AdmissionSearch();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
        ]);
    }

    public function actionCreate($patient_id = null)
    {
        $model = new Admission();
        $patientId = null;
        if ($patient_id !== null) {
            $patientId = filter_var($patient_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($patientId === false || Patient::findOne(['id' => $patientId]) === null) {
                throw new NotFoundHttpException('بیمار انتخاب‌شده پیدا نشد.');
            }
            $model->patient_id = $patientId;
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($patientId !== null) {
                $model->patient_id = $patientId;
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionView($id)
    {
        $admissionId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $model = $admissionId === false ? null : Admission::findOne(['id' => $admissionId]);
        if ($model === null) {
            throw new NotFoundHttpException('پذیرش پیدا نشد.');
        }
        return $this->render('view', ['model' => $model]);
    }
}
