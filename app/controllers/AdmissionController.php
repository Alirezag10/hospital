<?php

namespace app\controllers;

use app\models\Admission;
use app\models\AdmissionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AdmissionController extends Controller
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
        $searchModel = new AdmissionSearch();
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
        $model = new Admission();
        $model->loadDefaultValues();
        $model->status = 'admitted';

        if (
            $this->request->isPost
            && $model->load($this->request->post())
        ) {
            if ($model->status !== 'admitted') {
                $model->addError(
                    'status',
                    'پذیرش جدید باید با وضعیت بستری ثبت شود.'
                );
            } elseif ($model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $this->lockOpenAdmission($model->id);
                $model = $this->findModel($model->id);

                if ($model->load($this->request->post())) {
                    if ($model->status !== 'admitted') {
                        $model->addError(
                            'status',
                            'ترخیص فقط از صفحهٔ ثبت ترخیص انجام می‌شود.'
                        );
                    } elseif ($model->save()) {
                        $transaction->commit();

                        return $this->redirect([
                            'view',
                            'id' => $model->id,
                        ]);
                    }
                }

                $transaction->rollBack();
            } catch (\Throwable $error) {
                if ($transaction->getIsActive()) {
                    $transaction->rollBack();
                }

                throw $error;
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->lockOpenAdmission($model->id);

            if ($model->delete() !== 1) {
                throw new \RuntimeException('پذیرش حذف نشد.');
            }

            $transaction->commit();
        } catch (\Throwable $error) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }

            if (
                $error instanceof \yii\db\IntegrityException
                && (int) ($error->errorInfo[1] ?? 0) === 1451
            ) {
                Yii::$app->session->setFlash(
                    'error',
                    'این پذیرش رکورد وابسته دارد و قابل حذف نیست.'
                );
                return $this->redirect(['index']);
            }

            throw $error;
        }

        return $this->redirect(['index']);
    }

    public function actionSummary($id)
    {
        return $this->render('summary', [
            'model' => $this->findModel($id),
        ]);
    }

    private function lockOpenAdmission($id)
    {
        $db = Yii::$app->db;

        $row = $db->createCommand(
            'SELECT id, status
             FROM admissions
             WHERE id = :id
             FOR UPDATE',
            [':id' => $id]
        )->queryOne();

        if ($row === false) {
            throw new NotFoundHttpException('پذیرش پیدا نشد.');
        }

        $dischargeId = $db->createCommand(
            'SELECT id
             FROM discharges
             WHERE admission_id = :id
             FOR UPDATE',
            [':id' => $id]
        )->queryScalar();

        if (
            $row['status'] !== 'admitted'
            || $dischargeId !== false
        ) {
            throw new ForbiddenHttpException(
                'پذیرش ترخیص‌شده قابل ویرایش یا حذف نیست.'
            );
        }
    }

    protected function findModel($id)
    {
        $model = Admission::findOne(['id' => $id]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('پذیرش پیدا نشد.');
    }
}