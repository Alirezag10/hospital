<?php
namespace app\components;

use app\models\Admission;
use Yii;

final class AdmissionRegistration
{
    public static function save(Admission $model): bool
    {
        if (!$model->validate()) {
            return false;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // Every admission creator locks the same patient, including simultaneous requests.
            $patient = $db->createCommand(
                'SELECT id FROM patients WHERE id = :id FOR UPDATE',
                [':id' => $model->patient_id]
            )->queryScalar();
            if ($patient === false) {
                $model->addError('patient_id', 'بیمار انتخاب‌شده پیدا نشد.');
            } else {
                $existing = $db->createCommand(
                    "SELECT id FROM admissions
                     WHERE patient_id = :id AND status = 'admitted'
                     ORDER BY id LIMIT 1 FOR UPDATE",
                    [':id' => $model->patient_id]
                )->queryScalar();
                if ($existing !== false) {
                    $model->addError('patient_id', 'این بیمار پذیرش فعال دارد. ابتدا پروندهٔ فعال او را بررسی و ترخیص کنید.');
                } else {
                    // Serialize a status change with the final choice of doctor and ward.
                    $doctor = $db->createCommand(
                        'SELECT id FROM doctors WHERE id = :id AND active = 1 FOR UPDATE',
                        [':id' => $model->doctor_id]
                    )->queryScalar();
                    $ward = $db->createCommand(
                        'SELECT id FROM wards WHERE id = :id AND active = 1 FOR UPDATE',
                        [':id' => $model->ward_id]
                    )->queryScalar();

                    if ($doctor === false) {
                        $model->addError('doctor_id', 'پزشک انتخاب‌شده برای پذیرش جدید فعال نیست.');
                    }
                    if ($ward === false) {
                        $model->addError('ward_id', 'بخش انتخاب‌شده برای پذیرش جدید فعال نیست.');
                    }
                    if ($doctor !== false && $ward !== false && $model->save(false)) {
                        $transaction->commit();
                        return true;
                    }
                }
            }
            $transaction->rollBack();
            return false;
        } catch (\Throwable $error) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $error;
        }
    }
}
