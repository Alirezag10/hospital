<?php

namespace app\controllers;

use app\components\Access;
use app\components\AdmissionData;
use app\components\Billing;
use app\components\FormInput;
use app\components\RoleController;
use app\models\Admission;
use app\models\CorrectionForm;
use app\models\Discharge;
use app\models\MedicalItem;
use app\models\Prescription;
use app\models\PrescriptionItem;
use app\models\RecordChange;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ClinicalController extends RoleController
{
    public function actionCreate($admission_id)
    {
        return $this->form(new Prescription(), Access::admission($admission_id));
    }

    public function actionUpdate($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $model = $id === false ? null : Prescription::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('نسخه پیدا نشد.');
        }

        return $this->form($model, Access::admission($model->admission_id));
    }

    private function form(Prescription $model, $admission)
    {
        if ($admission->status !== 'admitted' || $admission->discharge !== null) {
            throw new ForbiddenHttpException('پس از ترخیص نمی‌توان نسخه یا اقلام درمانی را ثبت یا اصلاح کرد.');
        }

        $editing = !$model->isNewRecord;
        $originals = $editing ? $model->getItems()->indexBy('id')->all() : [];
        foreach ($originals as $original) {
            $original->sourceItemId = $original->id;
        }
        $items = array_values($originals);
        $correction = new CorrectionForm(['revision' => $editing ? $model->revision : 1]);

        if ($this->request->isPost && FormInput::load($model, $this->request->post())) {
            if ($editing) {
                FormInput::load($correction, $this->request->post());
                $submittedRevision = filter_var(
                    $correction->revision,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                if ($submittedRevision !== false && $submittedRevision !== (int) $model->revision) {
                    throw new ConflictHttpException('این نسخه هم‌زمان تغییر کرده است؛ صفحه را دوباره باز کنید.');
                }
            }

            $rows = $this->request->post('PrescriptionItem', []);
            if (!is_array($rows) || count($rows) > 50) {
                throw new BadRequestHttpException('در هر نسخه حداکثر ۵۰ قلم ثبت کنید.');
            }

            $items = [];
            $seen = [];
            $newSelections = [];
            $catalogIds = [];

            foreach (array_values($rows) as $row) {
                if (!is_array($row)) {
                    throw new BadRequestHttpException('ساختار اقلام معتبر نیست.');
                }

                $oldId = $row['item_id'] ?? null;
                $old = null;
                if ($oldId !== null && $oldId !== '') {
                    $oldId = filter_var($oldId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($oldId === false || !isset($originals[$oldId]) || isset($seen[$oldId])) {
                        throw new BadRequestHttpException('قلم انتخاب‌شده متعلق به این نسخه نیست یا تکرار شده است.');
                    }

                    $old = $originals[$oldId];
                    $seen[$oldId] = true;
                }

                $item = new PrescriptionItem();
                FormInput::load($item, ['PrescriptionItem' => $row]);
                $isBlank = !$item->medical_item_id
                    && trim((string) $item->title) === ''
                    && trim((string) $item->dosage) === ''
                    && trim((string) $item->instructions) === ''
                    && in_array((string) $item->quantity, ['', '1'], true);

                if ($isBlank && $old === null) {
                    continue;
                }

                // Existing snapshots retain their price even if the catalog tariff changed.
                $sameCatalogItem = $old !== null
                    && (string) $old->medical_item_id === (string) $item->medical_item_id
                    && $old->kind === $item->kind;
                if ($sameCatalogItem) {
                    $item->title = $old->title;
                    $item->unit_price = $old->unit_price;
                } else {
                    $catalogId = filter_var($item->medical_item_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $newSelections[] = [$item, $catalogId];
                    if ($catalogId !== false) {
                        $catalogIds[$catalogId] = $catalogId;
                    }
                }

                // This is only a form-row identity, never an assignable database primary key.
                $item->sourceItemId = $old?->id;
                $items[] = $item;
            }

            // Resolve all new selections with one query, independent of the number of form rows.
            $references = $catalogIds === []
                ? []
                : MedicalItem::find()
                    ->where(['id' => array_values($catalogIds), 'active' => 1])
                    ->indexBy('id')
                    ->all();

            foreach ($newSelections as [$item, $catalogId]) {
                $reference = $catalogId === false ? null : ($references[$catalogId] ?? null);
                if ($reference === null || $reference->kind !== $item->kind) {
                    $item->addError('medical_item_id', 'یک قلم فعال از فهرست انتخاب کنید.');
                    $item->title = '';
                    $item->unit_price = 0;
                } else {
                    $item->medical_item_id = $reference->id;
                    $item->title = $reference->title;
                    $item->unit_price = $reference->unit_price;
                    $item->newCatalogSelection = true;
                }
            }

            $valid = $model->validate();
            if ($editing) {
                $valid = $correction->validate() && $valid;
            }
            foreach ($items as $item) {
                $valid = $item->validate(null, false) && !$item->hasErrors() && $valid;
            }

            if ($valid && $this->savePrescription($model, $items, (int) $admission->id, $editing ? $correction : null)) {
                Yii::$app->session->setFlash(
                    'success',
                    $editing ? 'اصلاح نسخه با ثبت سابقه انجام شد.' : 'نسخه و اقلام درمانی ثبت شدند.'
                );
                return $this->redirect([
                    '/admission/view',
                    'id' => $admission->id,
                    '#' => 'prescription-' . $model->id,
                ]);
            }
        }

        if ($items === []) {
            $items[] = new PrescriptionItem(['kind' => 'medicine', 'quantity' => 1, 'unit_price' => 0]);
        }

        return $this->render('create', compact('model', 'items', 'admission', 'editing', 'correction', 'originals'));
    }

    private function savePrescription(
        Prescription $model,
        array $items,
        int $admissionId,
        ?CorrectionForm $correction
    ): bool {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $locked = Access::lockAdmission($admissionId);
            if ($locked['status'] !== 'admitted'
                || Discharge::find()->where(['admission_id' => $admissionId])->exists()) {
                throw new ForbiddenHttpException('پذیرش دیگر برای ثبت یا اصلاح نسخه در دسترس نیست.');
            }

            $catalogIds = [];
            foreach ($items as $item) {
                if ($item->newCatalogSelection) {
                    $catalogIds[] = (int) $item->medical_item_id;
                }
            }
            $catalogIds = array_unique($catalogIds);
            // IDs are validated integers; locking in ID order keeps concurrent catalog checks consistent.
            $references = $catalogIds
                ? MedicalItem::findBySql(
                    'SELECT * FROM medical_items WHERE id IN ('
                    . implode(',', $catalogIds) . ') ORDER BY id FOR UPDATE'
                )->indexBy('id')->all()
                : [];

            foreach ($items as $item) {
                if (!$item->newCatalogSelection) {
                    continue;
                }

                $reference = $references[$item->medical_item_id] ?? null;
                if ($reference === null || !$reference->active || $reference->kind !== $item->kind) {
                    $item->addError('medical_item_id', 'قلم انتخاب‌شده تغییر کرده یا غیرفعال شده است.');
                    $transaction->rollBack();
                    return false;
                }
                $item->title = $reference->title;
                $item->unit_price = $reference->unit_price;
            }

            $before = null;
            if ($correction !== null) {
                $current = Prescription::findOne(['id' => $model->id, 'admission_id' => $admissionId]);
                if ($current === null || (int) $current->revision !== (int) $correction->revision) {
                    throw new ConflictHttpException('این نسخه هم‌زمان تغییر کرده است؛ صفحه را دوباره باز کنید.');
                }

                $before = $current->attributes;
                $before['items'] = array_map(static fn($item) => $item->attributes, $current->getItems()->all());
                $model->revision = (int) $current->revision + 1;
            } else {
                $model->user_id = Yii::$app->user->id;
            }

            $model->admission_id = $admissionId;
            $model->doctor_id = $locked['doctor_id'];
            $model->save(false);

            if ($correction !== null) {
                PrescriptionItem::deleteAll(['prescription_id' => $model->id]);
            }
            foreach ($items as $item) {
                $item->prescription_id = $model->id;
                $item->save(false);
            }

            if (Billing::total($admissionId) === null) {
                $model->addError('diagnosis', 'مجموع هزینه‌های پرونده از محدوده قابل ثبت بیشتر می‌شود.');
                $transaction->rollBack();
                return false;
            }

            if ($correction !== null) {
                $after = $model->attributes;
                $after['items'] = array_map(static fn($item) => $item->attributes, $items);
                RecordChange::record(
                    $admissionId, 'prescription', (int) $model->id,
                    $correction->reason, $before, $after
                );
            }

            Admission::invalidateTreatmentApproval($admissionId);
            $transaction->commit();
            return true;
        } catch (\Throwable $error) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $error;
        }
    }

    public function actionPrint($admission_id)
    {
        $admission = Access::admission($admission_id);
        $this->layout = 'print';
        $data = AdmissionData::load($admission, true, true);

        return $this->render('print', [
            'admission' => $admission,
            'prescriptions' => $data['prescriptions'],
            'services' => $data['items'],
        ]);
    }
}
