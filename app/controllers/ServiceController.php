<?php
namespace app\controllers;

use app\components\FormInput;
use app\components\RoleController;
use app\models\Service;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class ServiceController extends RoleController
{
    public function actionIndex()
    {
        $term = $this->request->get('q', '');
        $term = is_string($term) ? mb_substr(trim($term), 0, 150) : '';
        $status = $this->request->get('active', '');
        $status = in_array($status, ['0', '1'], true) ? $status : '';
        $query = Service::find()
            ->andFilterWhere(['like', 'title', $term])
            ->andFilterWhere(['active' => $status]);

        return $this->render('index', [
            'term' => $term,
            'status' => $status,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['title' => SORT_ASC]],
                'pagination' => ['defaultPageSize' => 10, 'pageSizeLimit' => [10, 50]],
            ]),
        ]);
    }
    public function actionCreate()
    {
        return $this->form(new Service(['active' => 1]));
    }

    public function actionUpdate($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $model = $id === false ? null : Service::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('خدمت پیدا نشد.');
        }

        return $this->form($model);
    }

    private function form(Service $model)
    {
        if ($this->request->isPost && FormInput::load($model, $this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'فهرست خدمات به‌روزرسانی شد.');
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model]);
    }
}
