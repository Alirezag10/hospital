<?php

namespace app\controllers;

use app\components\Access;
use app\components\Billing;
use app\components\FormInput;
use app\components\RoleController;
use app\models\Admission;
use app\models\AdmissionService;
use app\models\CorrectionForm;
use app\models\Discharge;
use app\models\RecordChange;
use app\models\Service;
use Yii;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AdmissionServiceController extends RoleController
{
    public function actionCreate($admission_id = null)
    {
        $model = new AdmissionService(['quantity' => 1]);
        $admission = $admission_id === null ? null : Access::admission($admission_id);

        if ($admission !== null) {
            $model->admission_id = $admission->id;
        }

        return $this->form($model, $admission, $admission !== null);
    }

    public function actionUpdate($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $model = $id === false ? null : AdmissionService::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('خدمت پیدا نشد.');
        }

        return $this->form($model, Access::admission($model->admission_id), true);
    }

    private function form(AdmissionService $model, $selectedAdmission, bool $admissionLocked)
    {
        $editing = !$model->isNewRecord;
        if ($selectedAdmission !== null
            && ($selectedAdmission->status !== 'admitted' || $selectedAdmission->discharge !== null)) {
            throw new ForbiddenHttpException('پس از ترخیص نمی‌توان خدمت ثبت یا اصلاح کرد.');
        }

        $lockedId = $selectedAdmission?->id;
        $correction = new CorrectionForm(['revision' => $editing ? $model->revision : 1]);

        if ($this->request->isPost && FormInput::load($model, $this->request->post())) {
            if ($lockedId !== null) {
                $model->admission_id = $lockedId;
            }
            if ($editing) {
                FormInput::load($correction, $this->request->post());
            }

            if ((!$editing || $correction->validate())
                && $this->saveService($model, $editing ? $correction : null)) {
                Yii::$app->session->setFlash(
                    'success',
                    $editing ? 'خدمت با ثبت سابقه اصلاح شد.' : 'خدمت ثبت شد.'
                );
                return $this->redirect(['/admission/view', 'id' => $model->admission_id]);
            }
        }

        $admissionId = filter_var($model->admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($selectedAdmission === null && $admissionId !== false) {
            $selectedAdmission = Access::admission($model->admission_id);
        }

        $query = Service::find()->where(['active' => 1]);
        if ($editing) {
            $query->orWhere(['id' => $model->getOldAttribute('service_id')]);
        }
        $serviceModels = $query->orderBy(['title' => SORT_ASC])->all();

        return $this->render('create', compact(
            'model', 'admissionLocked', 'selectedAdmission',
            'serviceModels', 'editing', 'correction'
        ));
    }

    private function saveService(AdmissionService $model, ?CorrectionForm $correction): bool
    {
        if (!$model->validate(['admission_id', 'quantity'])) {
            return false;
        }

        $serviceId = filter_var($model->service_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($serviceId === false) {
            $model->addError('service_id', 'یک خدمت معتبر انتخاب کنید.');
            return false;
        }

        Access::admission($model->admission_id);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $locked = Access::lockAdmission((int) $model->admission_id);
            $alreadyDischarged = Discharge::find()->where(['admission_id' => $model->admission_id])->exists();
            if ($locked['status'] !== 'admitted' || $alreadyDischarged) {
                throw new ForbiddenHttpException('پذیرش دیگر قابل تغییر نیست.');
            }

            $before = null;
            $current = null;
            if ($correction !== null) {
                $current = AdmissionService::findOne([
                    'id' => $model->id,
                    'admission_id' => $model->admission_id,
                ]);
                if ($current === null || (int) $current->revision !== (int) $correction->revision) {
                    throw new ConflictHttpException('این خدمت هم‌زمان تغییر کرده است؛ صفحه را دوباره باز کنید.');
                }

                $before = $current->attributes;
                $model->revision = (int) $current->revision + 1;
            }

            $service = Service::findOne($serviceId);
            $sameService = $current !== null && (int) $current->service_id === $serviceId;
            if ($service === null || (!$sameService && !$service->active)) {
                $model->addError('service_id', 'یک خدمت فعال انتخاب کنید.');
                $transaction->rollBack();
                return false;
            }

            // Correcting quantity keeps the original tariff; a different service uses its current tariff.
            $model->service_id = $serviceId;
            $model->unit_price = $sameService ? $current->unit_price : $service->price;
            if ($current === null) {
                $model->created_at = $db->createCommand('SELECT NOW()')->queryScalar();
            }
            $model->save(false);

            if (Billing::total($model->admission_id) === null) {
                $model->addError('quantity', 'مجموع هزینه‌ها از محدوده قابل ثبت بیشتر می‌شود.');
                $transaction->rollBack();
                return false;
            }

            if ($correction !== null) {
                RecordChange::record(
                    (int) $model->admission_id,
                    'admission_service',
                    (int) $model->id,
                    $correction->reason,
                    $before,
                    $model->attributes
                );
            }

            Admission::invalidateTreatmentApproval((int) $model->admission_id);
            $transaction->commit();
            return true;
        } catch (\Throwable $error) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $error;
        }
    }
}
