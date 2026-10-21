<?php
namespace app\controllers;

use app\models\Patient;
use app\models\Admission;
use app\models\Discharge;
use app\components\RoleController as Controller;
use app\components\Access;
use app\models\User;
use app\models\LoginForm;
use app\components\FormInput;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ErrorAction;

class SiteController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), ['verbs' => [
            'class' => VerbFilter::class, 'actions' => ['logout' => ['post']],
        ]]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) { return $this->goHome(); }
        $this->layout = 'auth';
        $model = new LoginForm();
        if ($this->request->isPost && FormInput::load($model, $this->request->post()) && $model->login()) {
            return $this->goBack();
        }
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['login']);
    }
    public function actions()
    {
        return ['error' => ['class' => ErrorAction::class]];
    }

    public function actionIndex()
    {
        $today = date('Y-m-d');
        $tomorrow = (new \DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d');
        $activeAdmissions = Access::admissions()->andWhere(['status' => 'admitted']);
        $dischargesToday = Discharge::find()
            ->where(['>=', 'discharge_date', $today . ' 00:00:00'])
            ->andWhere(['<', 'discharge_date', $tomorrow . ' 00:00:00']);
        $stats = [['پذیرش‌های فعال', (int) $activeAdmissions->count(), 'admission', ['/admission/index', 'AdmissionSearch' => ['status' => 'admitted']]]];
        if (Access::is(User::ADMIN)) {
            $stats = [
                ['کاربران فعال', (int) User::find()->where(['is_active' => 1])->count(), 'patient', ['/user/index', 'status' => '1']],
                ['پذیرش‌های فعال', (int) $activeAdmissions->count(), 'admission', ['/admission/index', 'AdmissionSearch' => ['status' => 'admitted']]],
                ['ترخیص امروز', (int) $dischargesToday->count(), 'discharge', ['/admission/index', 'discharged_on' => $today]],
            ];
        } elseif (Access::is(User::SECRETARY)) {
            array_unshift($stats, ['بیماران', (int) Patient::find()->count(), 'patient', ['/patient/index']]);
            $stats[] = ['کل پذیرش‌ها', (int) Admission::find()->count(), 'admission', ['/admission/index']];
        } elseif (Access::is(User::DOCTOR)) {
            $stats[] = ['تمام پرونده‌های من', (int) Access::admissions()->count(), 'doctor', ['/admission/index']];
            $stats[] = ['نسخه‌های من', (int) \app\models\Prescription::find()->where(['doctor_id' => Yii::$app->user->identity->doctor_id])->count(), 'service', ['/admission/index']];
        } else {
            $stats[] = ['آمادهٔ ترخیص', (int) Admission::find()->where(['status'=>'admitted'])->andWhere(['not',['ready_at'=>null]])->count(), 'discharge', ['/discharge/create']];
            $stats[] = ['ترخیص امروز', (int) $dischargesToday->count(), 'discharge', ['/admission/index', 'discharged_on' => $today]];
            $stats[] = ['کل ترخیص‌ها', (int) Discharge::find()->count(), 'discharge', ['/admission/index', 'AdmissionSearch' => ['status' => 'discharged']]];
        }
        return $this->render('index', ['stats' => $stats]);
    }
}
