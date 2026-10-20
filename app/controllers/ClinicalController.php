<?php
namespace app\controllers;

use app\components\Access;
use app\components\Billing;
use app\components\FormInput;
use app\components\RoleController;
use app\components\ServiceAmount;
use app\models\Prescription;
use app\models\PrescriptionItem;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class ClinicalController extends RoleController
{
    public function actionCreate($admission_id)
    {
        $admission = Access::admission($admission_id);
        if ($admission->status !== 'admitted' || $admission->discharge !== null) {
            throw new ForbiddenHttpException('پس از ترخیص نمی‌توان نسخه یا اقلام درمانی ثبت کرد.');
        }
        $model = new Prescription();
        $items = [];
        if ($this->request->isPost && FormInput::load($model, $this->request->post())) {
            $rows = $this->request->post('PrescriptionItem', []);
            if (!is_array($rows) || count($rows) > 50) {
                throw new BadRequestHttpException('در هر نسخه حداکثر ۵۰ قلم ثبت کنید.');
            }
            foreach (array_values($rows) as $row) {
                if (!is_array($row)) { throw new BadRequestHttpException('ساختار اقلام نسخه معتبر نیست.'); }
                $item = new PrescriptionItem();
                FormInput::load($item, ['PrescriptionItem' => $row]);
                // Ignore only a completely untouched optional row.
                if (trim((string) $item->title) === '' && trim((string) $item->dosage) === ''
                    && trim((string) $item->instructions) === '' && in_array((string) $item->unit_price, ['', '0'], true)
                    && in_array((string) $item->quantity, ['', '1'], true)) { continue; }
                $items[] = $item;
            }
            $valid = $model->validate();
            $valid = Model::validateMultiple($items) && $valid;
            if ($valid && $this->savePrescription($model, $items, $admission->id)) {
                Yii::$app->session->setFlash('success', 'نسخه و اقلام درمانی ثبت شدند. از پرونده می‌توانید چاپ بگیرید.');
                return $this->redirect(['/admission/view', 'id' => $admission->id, '#' => 'prescription-' . $model->id]);
            }
        }
        if ($items === []) { $items[] = new PrescriptionItem(['kind' => 'medicine', 'quantity' => 1, 'unit_price' => 0]); }
        return $this->render('create', compact('model', 'items', 'admission'));
    }

    private function savePrescription(Prescription $model, array $items, int $admissionId): bool
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $lockedAdmission = Access::lockAdmission($admissionId);
            $doctorId = $lockedAdmission['doctor_id'];
            $status = $lockedAdmission['status'];
            if ($status !== 'admitted' || \app\models\Discharge::find()->where(['admission_id' => $admissionId])->exists()) {
                throw new ForbiddenHttpException('پذیرش دیگر برای ثبت نسخه در دسترس نیست.');
            }
            $total = Billing::total($admissionId);
            foreach ($items as $item) { $total = ServiceAmount::add($total, ServiceAmount::line($item->quantity, $item->unit_price)); }
            if ($total === null) {
                $model->addError('diagnosis', 'مجموع هزینه‌های پرونده از محدوده قابل ثبت بیشتر می‌شود.');
                $transaction->rollBack();
                return false;
            }
            $model->admission_id = $admissionId;
            $model->doctor_id = $doctorId;
            $model->user_id = Yii::$app->user->id;
            $model->save(false);
            foreach ($items as $item) {
                $item->prescription_id = $model->id;
                $item->save(false);
            }
            $transaction->commit();
            return true;
        } catch (\Throwable $error) {
            if ($transaction->isActive) { $transaction->rollBack(); }
            throw $error;
        }
    }

    public function actionPrint($admission_id)
    {
        $admission = Access::admission($admission_id);
        $this->layout = 'print';
        return $this->render('print', [
            'admission' => $admission,
            'prescriptions' => $admission->getPrescriptions()->with(['items', 'doctor', 'author'])->orderBy(['id' => SORT_ASC])->all(),
            'services' => $admission->getAdmissionServices()->with('service')->orderBy(['id' => SORT_ASC])->all(),
        ]);
    }
}
