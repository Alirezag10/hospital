<?php

namespace app\controllers;

use app\models\Doctor;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class DoctorController extends Controller
{
    public function actionIndex()
    {
        $term = $this->request->get('q', '');
        $term = is_string($term) ? mb_substr(trim($term), 0, 100) : '';
        $query = Doctor::find();
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
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'پزشک با موفقیت ثبت شد.');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }
}
