<?php

namespace app\controllers;

use app\components\FormInput;
use app\models\Ward;
use Yii;
use yii\data\ActiveDataProvider;
use app\components\RoleController as Controller;

class WardController extends Controller
{
    public function actionIndex()
    {
        $term = $this->request->get('q', '');
        $term = is_string($term) ? mb_substr(trim($term), 0, 100) : '';
        $query = Ward::find();
        foreach (preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
            $query->andWhere(['or', ['like', 'name', $part], ['like', 'floor', $part]]);
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
        $model = new Ward();
        if ($this->request->isPost && FormInput::load($model, $this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'بخش با موفقیت ثبت شد.');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $id = filter_var($id,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $model = $id === false ? null : Ward::findOne($id);
        if ($model === null) { throw new \yii\web\NotFoundHttpException('بخش پیدا نشد.'); }
        if ($this->request->isPost && FormInput::load($model,$this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success','مشخصات بخش به‌روزرسانی شد.');
            return $this->redirect(['index']);
        }
        return $this->render('create',['model'=>$model]);
    }
}
