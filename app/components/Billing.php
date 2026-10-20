<?php
namespace app\components;

use Yii;

final class Billing
{
    public static function total($admissionId): ?int
    {
        $services = Yii::$app->db->createCommand(
            'SELECT COALESCE(SUM(quantity * unit_price), 0) FROM admission_services WHERE admission_id = :id',
            [':id' => $admissionId]
        )->queryScalar();
        $supplies = Yii::$app->db->createCommand(
            'SELECT COALESCE(SUM(i.quantity * i.unit_price), 0) FROM prescription_items i
             INNER JOIN prescriptions p ON p.id = i.prescription_id WHERE p.admission_id = :id',
            [':id' => $admissionId]
        )->queryScalar();
        return ServiceAmount::add(ServiceAmount::fromDatabase($services), ServiceAmount::fromDatabase($supplies));
    }
}
