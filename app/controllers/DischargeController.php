<?php

namespace app\controllers;

use app\models\Discharge;
use app\models\Admission;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DischargeController extends Controller
{
    public function actionCreate($admission_id = null)
    {
        $model = new Discharge();
        $admissionId = null;

        if ($admission_id !== null) {
            $admissionId = filter_var($admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $admission = $admissionId === false ? null : Admission::findOne(['id' => $admissionId]);
            if ($admission === null) {
                throw new NotFoundHttpException('پذیرش انتخاب‌شده پیدا نشد.');
            }
            if ($this->request->isGet && ($admission->status !== 'admitted' || $admission->discharge !== null)) {
                throw new ForbiddenHttpException('این پذیرش قبلاً ترخیص شده است.');
            }
            $model->admission_id = $admissionId;
        }

        $isSubmitted = $this->request->isPost && $model->load($this->request->post());
        if ($isSubmitted && $admissionId !== null) {
            $model->admission_id = $admissionId;
        }
        if ($isSubmitted && $model->validate(['admission_id', 'description'])) {
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                $status = $db->createCommand(
                    'SELECT status
                     FROM admissions
                     WHERE id = :id
                     FOR UPDATE',
                    [':id' => $model->admission_id]
                )->queryScalar();

                $alreadyDischarged = Discharge::find()
                    ->where([
                        'admission_id' => $model->admission_id,
                    ])
                    ->exists();

                if ($alreadyDischarged) {
                    $model->addError(
                        'admission_id',
                        'برای این پذیرش قبلاً ترخیص ثبت شده است.'
                    );

                    $transaction->rollBack();
                } elseif ($status !== 'admitted') {
                    $model->addError(
                        'admission_id',
                        'یک پذیرش بستری و ترخیص‌نشده انتخاب کنید.'
                    );

                    $transaction->rollBack();
                } else {
                    $model->total_amount = (int) $db->createCommand(
                        'SELECT COALESCE(
                            SUM(quantity * unit_price),
                            0
                         )
                         FROM admission_services
                         WHERE admission_id = :id',
                        [':id' => $model->admission_id]
                    )->queryScalar();

                    $now = $db->createCommand(
                        'SELECT NOW()'
                    )->queryScalar();

                    $model->discharge_date = $now;
                    $model->created_at = $now;

                    if ($model->save()) {
                        $updated = $db->createCommand()->update(
                            'admissions',
                            ['status' => 'discharged'],
                            [
                                'id' => $model->admission_id,
                                'status' => 'admitted',
                            ]
                        )->execute();

                        if ($updated !== 1) {
                            throw new \RuntimeException(
                                'وضعیت پذیرش تغییر نکرد.'
                            );
                        }

                        $transaction->commit();

                        Yii::$app->session->setFlash('success', 'ترخیص بیمار با موفقیت ثبت شد.');

                        return $this->redirect([
                            '/admission/view',
                            'id' => $model->admission_id,
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

}
