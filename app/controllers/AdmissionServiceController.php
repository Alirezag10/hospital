<?php
namespace app\controllers;

use app\models\Admission;
use app\models\AdmissionService;
use app\models\Discharge;
use app\models\Service;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AdmissionServiceController extends Controller
{
    public function actionCreate($admission_id = null)
    {
        $model = new AdmissionService(['quantity' => 1]);
        $admissionId = null;
        if ($admission_id !== null) {
            $admissionId = filter_var($admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $admission = $admissionId === false ? null : Admission::findOne(['id' => $admissionId]);
            if ($admission === null) {
                throw new NotFoundHttpException('پذیرش انتخاب‌شده پیدا نشد.');
            }
            if ($admission->status !== 'admitted' || $admission->discharge !== null) {
                throw new ForbiddenHttpException('برای پذیرش ترخیص‌شده نمی‌توان خدمت ثبت کرد.');
            }
            $model->admission_id = $admissionId;
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($admissionId !== null) {
                $model->admission_id = $admissionId;
            }
            if ($this->saveService($model)) {
                Yii::$app->session->setFlash('success', 'خدمت با موفقیت برای این پذیرش ثبت شد.');
                return $this->redirect(['/admission/view', 'id' => $model->admission_id]);
            }
        }
        return $this->render('create', [
            'model' => $model,
            'admissionLocked' => $admissionId !== null,
        ]);
    }

    private function saveService(AdmissionService $model)
    {
        if (!$model->validate(['admission_id', 'service_id', 'quantity'])) {
            return false;
        }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $status = $db->createCommand('SELECT status FROM admissions WHERE id = :id FOR UPDATE', [':id' => $model->admission_id])->queryScalar();
            if ($status !== 'admitted' || Discharge::find()->where(['admission_id' => $model->admission_id])->exists()) {
                $model->addError('admission_id', 'ثبت خدمت فقط برای پذیرش بستری و ترخیص‌نشده مجاز است.');
                $transaction->rollBack();
                return false;
            }
            $service = Service::findOne(['id' => $model->service_id, 'active' => 1]);
            if ($service === null) {
                $model->addError('service_id', 'یک خدمت فعال انتخاب کنید.');
                $transaction->rollBack();
                return false;
            }
            $model->unit_price = $service->price;
            $model->created_at = $db->createCommand('SELECT NOW()')->queryScalar();
            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
            return true;
        } catch (\Throwable $error) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
            throw $error;
        }
    }
}
