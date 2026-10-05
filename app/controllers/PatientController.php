<?php
namespace app\controllers;

use app\models\Patient;
use app\models\PatientSearch;
use yii\web\Controller;

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
            return $this->redirect(['/admission/create', 'patient_id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }
}
