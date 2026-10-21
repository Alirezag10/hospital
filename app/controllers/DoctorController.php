<?php

namespace app\controllers;

use app\components\FormInput;
use app\models\Doctor;
use Yii;
use yii\data\ActiveDataProvider;
use app\components\RoleController as Controller;

class DoctorController extends Controller
{
    public function actionIndex()
    {
        $term = $this->request->get('q', '');
        $term = is_string($term) ? mb_substr(trim($term), 0, 100) : '';
        $query = Doctor::find()->with('user');
        foreach (preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
            $query->andWhere(['or', ['like', 'name', $part], ['like', 'specialty', $part], ['like', 'mobile', $part]]);
        }
        return $this->render('index', [
            'term' => $term,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
                'pagination' => ['defaultPageSize' => 10, 'pageSizeLimit' => [10, 50]],
            ]),
        ]);
    }

    public function actionCreate()
    {
        $model = new Doctor();
        if ($this->request->isPost && FormInput::load($model, $this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'پزشک با موفقیت ثبت شد.');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionAccount($id)
    {
        return $this->redirect(['/user/create', 'doctor_id' => $id]);
    }

    public function actionUpdate($id)
    {
        $id = filter_var($id,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $model = $id === false ? null : Doctor::findOne($id);
        if ($model === null) { throw new \yii\web\NotFoundHttpException('پزشک پیدا نشد.'); }
        if ($this->request->isPost && FormInput::load($model,$this->request->post()) && $model->validate()) {
            $tx = Yii::$app->db->beginTransaction();
            try {
                // Match account edits' lock order before updating the profile and its user.
                Yii::$app->db->createCommand("SELECT id FROM users WHERE role='admin' AND is_active=1 ORDER BY id FOR UPDATE")->queryColumn();
                if ($model->save(false)) {
                    \app\models\User::updateAll(['full_name'=>$model->name], ['id' => Doctor::find()->select('user_id')->where(['id' => $model->id])]);
                    $tx->commit();
                    Yii::$app->session->setFlash('success','مشخصات پزشک و نام حساب متصل به‌روزرسانی شد.');
                    return $this->redirect(['index']);
                }
                $tx->rollBack();
            } catch (\Throwable $error) {
                if ($tx->isActive) { $tx->rollBack(); }
                throw $error;
            }
        }
        return $this->render('create',['model'=>$model]);
    }
}
