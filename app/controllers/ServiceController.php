<?php

namespace app\controllers;

use app\models\Service;
use app\models\ServiceSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ServiceController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new ServiceSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Service();

        if ($this->request->isPost) {
            if (
                $model->load($this->request->post())
                && $model->save()
            ) {
                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (
            $this->request->isPost
            && $model->load($this->request->post())
            && $model->save()
        ) {
            return $this->redirect([
                'view',
                'id' => $model->id,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $model->delete();
        } catch (\yii\db\IntegrityException $error) {
            // MySQL: a referenced record cannot be deleted.
            if ((int) ($error->errorInfo[1] ?? 0) !== 1451) {
                throw $error;
            }

            \Yii::$app->session->setFlash('error', 'این خدمت در پذیرش استفاده شده است؛ به‌جای حذف آن را غیرفعال کنید.');
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = Service::findOne(['id' => $id]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException(
            'خدمت پیدا نشد.'
        );
    }
}