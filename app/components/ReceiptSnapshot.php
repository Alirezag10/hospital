<?php
namespace app\components;

use app\models\Admission;
use app\models\Discharge;
use Yii;

final class ReceiptSnapshot
{
    public static function capture(Admission $admission, Discharge $discharge): string
    {
        $data = AdmissionData::load($admission, false, true);
        $patient = $admission->patient;
        $services = [];
        foreach ($data['items'] as $row) {
            $services[] = ['title'=>$row->service?->title ?? 'خدمت', 'quantity'=>(int)$row->quantity,
                'unit_price'=>(int)$row->unit_price, 'amount'=>ServiceAmount::line($row->quantity,$row->unit_price)];
        }
        $items = [];
        foreach ($data['costItems'] as $row) {
            // Financial snapshot deliberately excludes diagnoses and consumption instructions.
            $items[] = ['kind'=>$row->kind, 'title'=>$row->title, 'quantity'=>(int)$row->quantity,
                'unit_price'=>(int)$row->unit_price, 'amount'=>ServiceAmount::line($row->quantity,$row->unit_price)];
        }
        return json_encode([
            'version'=>1, 'hospital'=>Yii::$app->name, 'receipt_id'=>(int)$discharge->id,
            'admission_id'=>(int)$admission->id, 'patient_name'=>$patient->first_name.' '.$patient->last_name,
            'national_code'=>$patient->national_code, 'doctor'=>$admission->doctor_name, 'ward'=>$admission->ward,
            'admission_date'=>$admission->admission_date, 'discharge_date'=>$discharge->discharge_date,
            'services'=>$services, 'items'=>$items, 'bill'=>$data['bill'],
            'final'=>ServiceAmount::fromDatabase($discharge->total_amount),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public static function read(Discharge $discharge): array
    {
        $snapshot = json_decode((string)$discharge->receipt_data, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($snapshot) || ($snapshot['version'] ?? null) !== 1) {
            throw new \RuntimeException('اطلاعات رسید نهایی معتبر نیست.');
        }
        return $snapshot;
    }
}
