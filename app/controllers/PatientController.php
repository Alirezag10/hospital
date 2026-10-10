<?php
namespace app\controllers;

use app\models\Patient;
use app\models\PatientSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PatientController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new PatientSearch();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
        ]);
    }

    public function actionCreate()
    {
        $model = new Patient();
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'بیمار با موفقیت ثبت شد. اکنون می‌توانید پذیرش او را ثبت کنید.');
            return $this->redirect(['/admission/create', 'patient_id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionView($id)
    {
        $patientId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $patient = $patientId === false ? null : Patient::findOne(['id' => $patientId]);
        if ($patient === null) {
            throw new NotFoundHttpException('بیمار پیدا نشد.');
        }

        $admissions = new ActiveDataProvider([
            'query' => $patient->getAdmissions()->with('discharge')->orderBy(['id' => SORT_DESC]),
            'pagination' => ['defaultPageSize' => 10, 'pageSizeLimit' => [10, 50]],
        ]);

        return $this->render('view', ['model' => $patient, 'admissions' => $admissions]);
    }
}
