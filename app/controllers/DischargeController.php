<?php

namespace app\controllers;

use app\components\Access;
use app\components\AdmissionData;
use app\components\FormInput;
use app\components\ReceiptSnapshot;
use app\models\Discharge;
use app\models\Admission;
use Yii;
use app\components\RoleController as Controller;
use app\components\Billing;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServiceUnavailableHttpException;

class DischargeController extends Controller
{
    public function actionCreate($admission_id = null)
    {
        $model = new Discharge();
        $admissionId = null;
        $admission = null;

        if ($admission_id !== null) {
            $admissionId = filter_var($admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $admission = $admissionId === false
                ? null
                : Admission::find()
                    ->with(['patient', 'discharge'])
                    ->where(['id' => $admissionId])
                    ->one();
            if ($admission === null) {
                throw new NotFoundHttpException('پذیرش انتخاب‌شده پیدا نشد.');
            }
            if ($this->request->isGet && ($admission->status !== 'admitted' || $admission->discharge !== null)) {
                throw new ForbiddenHttpException('این پذیرش قبلاً ترخیص شده است.');
            }
            $model->admission_id = $admissionId;
        }

        $isSubmitted = $this->request->isPost && FormInput::load($model, $this->request->post());
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
                } elseif (!Admission::find()
                    ->where(['id' => $model->admission_id])
                    ->andWhere(['not', ['ready_at' => null]])
                    ->exists()) {
                    $model->addError('admission_id', 'پایان درمان هنوز توسط پزشک تأیید نشده است.');
                    $transaction->rollBack();
                } else {
                    $model->total_amount = Billing::total($model->admission_id);
                    if ($model->total_amount === null) {
                        $model->addError('admission_id', 'جمع هزینه‌ها از محدودهٔ قابل ثبت بیشتر است؛ ریز خدمات را بررسی کنید.');
                        $transaction->rollBack();
                        return $this->renderForm($model, $admission);
                    }

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

                        $finalAdmission = Admission::findOne($model->admission_id);
                        $model->receipt_data = ReceiptSnapshot::capture($finalAdmission, $model);
                        $model->save(false, ['receipt_data']);

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

        return $this->renderForm($model, $admission);
    }

    private function renderForm(Discharge $model, ?Admission $admission)
    {
        $admissionId = filter_var($model->admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($admission === null && $admissionId !== false) {
            $admission = Admission::find()
                ->with(['patient', 'discharge'])
                ->where(['id' => $model->admission_id])
                ->one();
        }

        $data = $admission === null
            ? ['items' => [], 'costItems' => [], 'bill' => Billing::breakdown([], [])]
            : AdmissionData::load($admission, false, true);

        return $this->render('create', array_merge($data, [
            'model' => $model,
            'selectedAdmission' => $admission,
        ]));
    }

    public function actionReceipt($admission_id)
    {
        $admission = Access::admission($admission_id);
        if ($admission->discharge === null) {
            throw new NotFoundHttpException('رسید فقط پس از ثبت ترخیص در دسترس است.');
        }

        $this->layout = 'print';
        if ($admission->discharge->receipt_data === null) {
            throw new ServiceUnavailableHttpException('اطلاعات رسید نهایی این پذیرش موجود نیست؛ برای بررسی پرونده با مدیر سیستم تماس بگیرید.');
        }

        return $this->render('receipt', ['snapshot' => ReceiptSnapshot::read($admission->discharge)]);
    }
}
