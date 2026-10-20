<?php
namespace app\controllers;

use app\components\FormInput;
use app\components\RoleController;
use app\models\Doctor;
use app\models\User;
use app\models\UserForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class UserController extends RoleController
{
    public function actionIndex()
    {
        $term = $this->request->get('q', '');
        $term = is_string($term) ? mb_substr(trim($term), 0, 100) : '';
        $role = $this->request->get('role', '');
        $role = is_string($role) && array_key_exists($role, User::roleLabels()) ? $role : '';
        $status = $this->request->get('status', '');
        $status = in_array($status, ['0', '1'], true) ? $status : '';
        $query = User::find()->with('doctor')->andFilterWhere(['role' => $role]);
        if ($status !== '') { $query->andWhere(['is_active' => (int) $status]); }
        if ($term !== '') { $query->andWhere(['or', ['like', 'username', $term], ['like', 'full_name', $term]]); }
        return $this->render('index', [
            'term' => $term, 'role' => $role, 'status' => $status,
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
                'pagination' => ['defaultPageSize' => 10, 'pageSizeLimit' => [10, 50]]]),
        ]);
    }

    public function actionCreate($doctor_id = null)
    {
        $doctor = null;
        if ($doctor_id !== null) {
            $id = filter_var($doctor_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $doctor = $id === false ? null : Doctor::findOne(['id' => $id]);
            if ($doctor === null || $doctor->user !== null) { throw new NotFoundHttpException('پزشک پیدا نشد یا از قبل حساب دارد.'); }
        }
        $model = new UserForm();
        if ($doctor !== null) { $model->role = User::DOCTOR; $model->doctor_id = $doctor->id; $model->full_name = $doctor->name; }
        if ($this->request->isPost && FormInput::load($model, $this->request->post())) {
            if ($doctor !== null) { $model->role = User::DOCTOR; $model->doctor_id = $doctor->id; }
            if ($model->save() !== null) {
                Yii::$app->session->setFlash('success', 'حساب کاربر ساخته شد.');
                return $this->redirect(['index']);
            }
        }
        $model->password = $model->password_repeat = '';
        return $this->render('form', ['model' => $model, 'doctorLocked' => $doctor !== null]);
    }

    public function actionUpdate($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $user = $id === false ? null : User::findOne(['id' => $id]);
        if ($user === null) { throw new NotFoundHttpException('کاربر پیدا نشد.'); }
        $model = new UserForm(['user' => $user, 'username' => $user->username, 'full_name' => $user->full_name,
            'role' => $user->role, 'is_active' => $user->is_active, 'doctor_id' => $user->doctor_id]);
        if ($this->request->isPost && FormInput::load($model, $this->request->post())) {
            $saved = $model->save();
            if ($saved !== null) {
                if ((int) Yii::$app->user->id === (int) $saved->id) {
                    Yii::$app->user->logout(false);
                    if ((int) $saved->is_active === 1) { Yii::$app->user->login($saved); }
                }
                Yii::$app->session->setFlash('success', 'اطلاعات حساب به‌روزرسانی شد.');
                return $this->redirect(\app\components\Access::is(User::ADMIN) ? ['index'] : ['/site/index']);
            }
        }
        $model->password = $model->password_repeat = '';
        return $this->render('form', ['model' => $model, 'doctorLocked' => false]);
    }
}
