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
        $today = date('Y-m-d');
        $tomorrow = (new \DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d');
        $activeAdmissions = Admission::find()->where(['status' => 'admitted']);
        $dischargesToday = Discharge::find()
            ->where(['>=', 'discharge_date', $today . ' 00:00:00'])
            ->andWhere(['<', 'discharge_date', $tomorrow . ' 00:00:00']);
        return $this->render('index', [
            'patients' => Patient::find()->count(),
            'activeAdmissions' => (clone $activeAdmissions)->count(),
            'dischargesToday' => $dischargesToday->count(),
            'today' => $today,
        ]);
    }
}
