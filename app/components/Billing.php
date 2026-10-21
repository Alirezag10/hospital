<?php
namespace app\components;

use Yii;

final class Billing
{
    public static function breakdown(array $services, array $items): array
    {
        $totals=['services'=>0,'medicine'=>0,'supply'=>0];
        foreach ($services as $row) { $totals['services']=ServiceAmount::add($totals['services'],ServiceAmount::line($row->quantity,$row->unit_price)); }
        foreach ($items as $row) { $totals[$row->kind]=ServiceAmount::add($totals[$row->kind],ServiceAmount::line($row->quantity,$row->unit_price)); }
        $totals['total']=ServiceAmount::add(ServiceAmount::add($totals['services'],$totals['medicine']),$totals['supply']);
        return $totals;
    }
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
