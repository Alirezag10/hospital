<?php
namespace app\controllers;

use app\models\Patient;
use app\models\Admission;
use app\models\Discharge;
use yii\web\Controller;
use yii\web\ErrorAction;

class SiteController extends Controller
{
    public function actions()
    {
        return ['error' => ['class' => ErrorAction::class]];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'patients' => Patient::find()->count(),
            'activeAdmissions' => Admission::find()->where(['status' => 'admitted'])->count(),
            'discharges' => Discharge::find()->count(),
        ]);
    }
}
