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
        $activeAdmissions = Admission::find()->where(['status' => 'admitted']);
        $dischargesToday = Discharge::find()
            ->where(['>=', 'discharge_date', new Expression('CURDATE()')])
            ->andWhere(['<', 'discharge_date', new Expression('CURDATE() + INTERVAL 1 DAY')]);
        return $this->render('index', [
            'patients' => Patient::find()->count(),
            'activeAdmissions' => (clone $activeAdmissions)->count(),
            'dischargesToday' => $dischargesToday->count(),
        ]);
    }
}
