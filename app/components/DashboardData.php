<?php

namespace app\components;

use app\models\Discharge;
use app\models\Patient;
use app\models\User;
use DateTimeImmutable;

final class DashboardData
{
    private const PREVIEW_LIMIT = 8;

    public static function load(): array
    {
        $today = date('Y-m-d');
        $tomorrow = (new DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d');
        $active = Access::admissions()->andWhere(['status' => 'admitted']);
        $activeRoute = ['/admission/index', 'AdmissionSearch' => ['status' => 'admitted']];
        $stats = [['پذیرش‌های فعال', (int) (clone $active)->count(), 'admission', $activeRoute]];
        $admissions = [];
        $total = 0;

        if (Access::is(User::DOCTOR)) {
            $stats[] = ['تمام پرونده‌های من', (int) Access::admissions()->count(), 'doctor', ['/admission/index']];
            $readyCount = (int) (clone $active)->andWhere(['not', ['ready_at' => null]])->count();
            $stats[] = ['آمادهٔ ترخیص', $readyCount, 'discharge', '#dashboard-worklist'];
            $total = (int) $stats[0][1];
            $admissions = (clone $active)->with('patient')->orderBy([
                'ready_at' => SORT_ASC, 'admission_date' => SORT_ASC, 'id' => SORT_ASC,
            ])->limit(self::PREVIEW_LIMIT)->all();
        } elseif (Access::is(User::SECRETARY)) {
            array_unshift($stats, ['بیماران', (int) Patient::find()->count(), 'patient', ['/patient/index']]);
            $stats[] = ['کل پذیرش‌ها', (int) Access::admissions()->count(), 'admission', ['/admission/index']];
        } else {
            $dischargesToday = Discharge::find()
                ->where(['>=', 'discharge_date', $today . ' 00:00:00'])
                ->andWhere(['<', 'discharge_date', $tomorrow . ' 00:00:00']);
            $todayRoute = ['/admission/index', 'discharged_on' => $today];
            if (Access::is(User::ADMIN)) {
                array_unshift($stats, ['کاربران فعال', (int) User::find()->where(['is_active' => 1])->count(), 'patient', ['/user/index', 'status' => '1']]);
                $stats[] = ['ترخیص امروز', (int) $dischargesToday->count(), 'discharge', $todayRoute];
            } else {
                $readyCount = (int) (clone $active)
                    ->leftJoin(['d' => Discharge::tableName()], 'd.admission_id = admissions.id')
                    ->andWhere(['d.id' => null])->andWhere(['not', ['ready_at' => null]])->count();
                $stats = [
                    ['آمادهٔ ترخیص', $readyCount, 'discharge', ['/discharge/create']],
                    ['ترخیص امروز', (int) $dischargesToday->count(), 'discharge', $todayRoute],
                    ['کل ترخیص‌ها', (int) Discharge::find()->count(), 'admission', ['/admission/index', 'AdmissionSearch' => ['status' => 'discharged']]],
                ];
            }
        }

        return compact('stats', 'admissions', 'total');
    }
}
