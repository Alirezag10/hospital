<?php
namespace app\components;

use app\models\Admission;
use app\models\Prescription;
use app\models\PrescriptionItem;
use app\models\RecordChange;
use Yii;

final class AdmissionData
{
    public static function load(Admission $admission, bool $clinical, bool $billing): array
    {
        $items=$billing ? $admission->getAdmissionServices()->with('service')->orderBy(['id'=>SORT_ASC])->all() : [];
        $prescriptions=$clinical ? $admission->getPrescriptions()->with(['items','doctor','author'])->orderBy(['id'=>SORT_ASC])->all() : [];
        $costItems=[];
        if ($clinical) {
            foreach ($prescriptions as $p) { array_push($costItems,...$p->items); }
        } elseif ($billing) {
            $costItems=PrescriptionItem::find()->alias('i')->select('i.*')->innerJoin(['p'=>Prescription::tableName()],'p.id=i.prescription_id')
                ->where(['p.admission_id'=>$admission->id])->orderBy(['i.id'=>SORT_ASC])->all();
        }
        $bill=Billing::breakdown($items,$costItems);
        $timeline=[['date'=>$admission->admission_date,'title'=>'ثبت پذیرش','detail'=>'پذیرش در '.$admission->ward.' توسط '.$admission->doctor_name]];
        foreach ($items as $row) { $timeline[]=['date'=>$row->created_at,'title'=>'ثبت خدمت','detail'=>($row->service?->title??'خدمت').' · تعداد '.Yii::$app->formatter->asInteger($row->quantity)]; }
        foreach ($prescriptions as $p) { $timeline[]=['date'=>$p->created_at,'title'=>'ثبت نسخه','detail'=>'نسخه '.Yii::$app->formatter->asInteger($p->id).' · ثبت‌کننده: '.($p->author?->full_name??'نامشخص')]; }
        if ($clinical || $billing) {
            $query=RecordChange::find()->with('actor')->where(['admission_id'=>$admission->id]);
            if (!$clinical) { $query->andWhere(['entity_type'=>'admission_service']); }
            foreach ($query->orderBy(['id'=>SORT_ASC])->all() as $change) {
                $timeline[]=['date'=>$change->created_at,'title'=>$change->entity_type==='prescription'?'اصلاح نسخه':'اصلاح خدمت',
                    'detail'=>$change->actor->full_name.' · '.$change->reason];
            }
        }
        if ($admission->discharge!==null) {
            $timeline[]=['date'=>$admission->discharge->discharge_date,'title'=>'ثبت ترخیص','detail'=>$billing?'مبلغ نهایی '.Yii::$app->formatter->asInteger($admission->discharge->total_amount).' تومان':'پایان بستری'];
        }
        if ($admission->ready_at !== null) {
            $timeline[]=['date'=>$admission->ready_at,'title'=>'تأیید پایان درمان',
                'detail'=>'تأییدکننده: '.($admission->readyBy?->full_name ?? 'کاربر ثبت‌کننده')];
        }
        usort($timeline,static fn($a,$b)=>strcmp((string)$a['date'],(string)$b['date']));
        return compact('items','prescriptions','costItems','bill','timeline');
    }
}
