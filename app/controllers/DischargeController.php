<?php

namespace app\controllers;

use Yii;
use app\models\Discharge;
use app\models\DischargeSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DischargeController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new DischargeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

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
        $model = new Discharge();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                $status = $db->createCommand(
                    'SELECT status FROM admissions WHERE id = :id FOR UPDATE',
                    [':id' => (int) $model->admission_id]
                )->queryScalar();

                if ($status !== 'admitted') {
                    $model->addError(
                        'admission_id',
                        'یک پذیرش فعال و ترخیص‌نشده انتخاب کنید.'
                    );
                    $transaction->rollBack();
                } else {
                    $model->total_amount = (int) $db->createCommand(
                        'SELECT COALESCE(SUM(quantity * unit_price), 0)
                         FROM admission_services WHERE admission_id = :id',
                        [':id' => (int) $model->admission_id]
                    )->queryScalar();

                    $model->discharge_date = $db
                        ->createCommand('SELECT NOW()')
                        ->queryScalar();

                    if ($model->save()) {
                        $updated = $db->createCommand()->update(
                            'admissions',
                            ['status' => 'discharged'],
                            ['id' => $model->admission_id, 'status' => 'admitted']
                        )->execute();

                        if ($updated !== 1) {
                            throw new \RuntimeException(
                                'وضعیت پذیرش به‌روزرسانی نشد.'
                            );
                        }

                        $transaction->commit();

                        return $this->redirect([
                            'view',
                            'id' => $model->id,
                        ]);
                    }

                    $transaction->rollBack();
                }
            } catch (\Throwable $error) {
                if ($transaction->getIsActive()) {
                    $transaction->rollBack();
                }

                throw $error;
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        throw new ForbiddenHttpException('ترخیص ثبت‌شده قابل ویرایش نیست.');
    }

    public function actionDelete($id)
    {
        throw new ForbiddenHttpException('ترخیص ثبت‌شده قابل حذف نیست.');
    }

    protected function findModel($id)
    {
        $model = Discharge::findOne(['id' => $id]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ترخیص موردنظر پیدا نشد.');
    }
}