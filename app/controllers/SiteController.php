<?php
namespace app\controllers;

use app\components\DashboardData;
use app\components\RoleController as Controller;
use app\models\LoginForm;
use app\components\FormInput;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ErrorAction;

class SiteController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['post'], 'dashboard' => ['get']],
            ],
        ]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'auth';
        $model = new LoginForm();
        if ($this->request->isPost
            && FormInput::load($model, $this->request->post())
            && $model->login()) {
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
        return $this->render('index', ['dashboard' => DashboardData::load()]);
    }

    public function actionDashboard()
    {
        Yii::$app->response->headers->set('Cache-Control', 'no-store, private');

        return $this->asJson([
            'html' => $this->renderPartial('_dashboard', DashboardData::load()),
        ]);
    }
}
