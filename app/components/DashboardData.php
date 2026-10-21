<?php

namespace app\components;

use app\models\Discharge;
use app\models\Patient;
use app\models\User;
use DateTimeImmutable;
use DateTimeZone;
use Yii;

final class DashboardData
{
    private const PREVIEW_LIMIT = 8;

    public static function load(): array
    {
        $today = date('Y-m-d');
        $tomorrow = (new DateTimeImmutable($today))->modify('+1 day')->format('Y-m-d');
        $active = Access::admissions()->andWhere(['status' => 'admitted']);
        $activeCount = (int) $active->count();
        $activeRoute = ['/admission/index', 'AdmissionSearch' => ['status' => 'admitted']];
        $stats = [['پذیرش‌های فعال', $activeCount, 'admission', $activeRoute]];
        $query = Access::admissions()->with('patient')->orderBy(['id' => SORT_DESC]);
        $heading = 'پذیرش‌های اخیر';
        $description = 'آخرین پرونده‌های ثبت‌شده؛ برای مشاهده جزئیات، پرونده را باز کنید.';
        $emptyMessage = 'هنوز پذیرشی ثبت نشده است. پس از ثبت بیمار، پذیرش او را ایجاد کنید.';
        $listRoute = ['/admission/index'];
        $showWaiting = false;

        if (Access::is(User::DOCTOR)) {
            // Scope both the count and the preview to the signed-in doctor's assigned admissions.
            $query = (clone $active)->with('patient')->orderBy([
                'ready_at' => SORT_ASC,
                'admission_date' => SORT_ASC,
                'id' => SORT_ASC,
            ]);
            $heading = 'پرونده‌های فعال من';
            $description = 'پرونده‌های در انتظار تکمیل درمان ابتدا نمایش داده می‌شوند.';
            $emptyMessage = 'در حال حاضر پذیرش فعالی به شما اختصاص ندارد.';
            $listRoute = $activeRoute;
            $stats[] = ['تمام پرونده‌های من', (int) Access::admissions()->count(), 'doctor', ['/admission/index']];
            $readyCount = (int) (clone $active)->andWhere(['not', ['ready_at' => null]])->count();
            $stats[] = ['آمادهٔ ترخیص', $readyCount, 'discharge', '#dashboard-worklist'];
        } elseif (Access::is(User::SECRETARY)) {
            array_unshift($stats, ['بیماران', (int) Patient::find()->count(), 'patient', ['/patient/index']]);
            $stats[] = ['کل پذیرش‌ها', (int) Access::admissions()->count(), 'admission', ['/admission/index']];
        } else {
            $dischargesToday = Discharge::find()
                ->where(['>=', 'discharge_date', $today . ' 00:00:00'])
                ->andWhere(['<', 'discharge_date', $tomorrow . ' 00:00:00']);
            $todayRoute = ['/admission/index', 'discharged_on' => $today];

            if (Access::is(User::ADMIN)) {
                $activeUsers = (int) User::find()->where(['is_active' => 1])->count();
                array_unshift($stats, [
                    'کاربران فعال', $activeUsers, 'patient', ['/user/index', 'status' => '1'],
                ]);
                $stats[] = ['ترخیص امروز', (int) $dischargesToday->count(), 'discharge', $todayRoute];
            } else {
                $query = DischargeQueue::query()->with('patient');
                $heading = 'صف آمادهٔ ترخیص';
                $description = 'پایان درمان این پرونده‌ها تأیید شده است؛ طولانی‌ترین انتظار در ابتدای صف قرار دارد.';
                $emptyMessage = 'پرونده‌ای در صف ترخیص نیست. پس از تأیید پایان درمان توسط پزشک، پرونده اینجا نمایش داده می‌شود.';
                $listRoute = ['/discharge/create'];
                $showWaiting = true;
                $stats = [
                    ['آمادهٔ ترخیص', (int) (clone $query)->count(), 'discharge', '#dashboard-worklist'],
                    ['ترخیص امروز', (int) $dischargesToday->count(), 'discharge', $todayRoute],
                    [
                        'کل ترخیص‌ها', (int) Discharge::find()->count(), 'admission',
                        ['/admission/index', 'AdmissionSearch' => ['status' => 'discharged']],
                    ],
                ];
            }
        }

        $total = (int) (clone $query)->count();
        $admissions = $query->limit(self::PREVIEW_LIMIT)->all();

        return compact(
            'stats', 'admissions', 'total', 'heading', 'description',
            'emptyMessage', 'listRoute', 'showWaiting'
        );
    }

    public static function waitingTime(string $readyAt, ?DateTimeImmutable $now = null): string
    {
        $zone = new DateTimeZone(Yii::$app->timeZone);
        $now = $now ?? new DateTimeImmutable('now', $zone);
        $ready = new DateTimeImmutable($readyAt, $zone);
        $minutes = max(0, intdiv($now->getTimestamp() - $ready->getTimestamp(), 60));

        if ($minutes === 0) {
            return 'کمتر از یک دقیقه';
        }

        $parts = [];
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;

        foreach ([[$days, 'روز'], [$hours, 'ساعت'], [$remainingMinutes, 'دقیقه']] as [$value, $unit]) {
            if ($value > 0) {
                $parts[] = Yii::$app->formatter->asInteger($value) . ' ' . $unit;
            }
        }

        return implode(' و ', $parts);
    }
}
