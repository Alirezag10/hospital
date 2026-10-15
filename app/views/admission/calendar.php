<?php
use yii\helpers\Html;

$this->title = 'تقویم پذیرش‌ها';
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$cellCount = (int) (ceil(($startOffset + $daysInMonth) / 7) * 7);
$monthNames = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
[$jalaliYear, $jalaliMonth] = array_map('intval', explode('-', $month));
$persianDigits = static fn(int $number): string => strtr((string) $number, [
    '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
    '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
]);
$monthTitle = $monthNames[$jalaliMonth - 1] . ' ' . $persianDigits($jalaliYear);
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>پذیرش‌های ثبت‌شده در هر روز را ببینید.</p></div>
    <?= Html::a('فهرست پذیرش‌ها', ['index'], ['class' => 'btn btn-outline-primary']) ?>
</div>
<section class="calendar-panel" aria-label="تقویم پذیرش‌ها">
    <div class="calendar-toolbar">
        <?= Html::a('ماه قبل', ['calendar', 'month' => $previousMonth], ['class' => 'btn btn-outline-secondary']) ?>
        <div class="calendar-heading">
            <h2><?= Html::encode($monthTitle) ?></h2>
            <?= Html::a('ماه جاری', ['calendar', 'month' => $currentMonth], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
        <?= Html::a('ماه بعد', ['calendar', 'month' => $nextMonth], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <div class="calendar-grid" role="grid" aria-label="روزهای ماه">
        <div class="calendar-row" role="row">
        <?php foreach (['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'] as $weekday): ?>
            <div class="calendar-weekday" role="columnheader"><?= Html::encode($weekday) ?></div>
        <?php endforeach; ?>
        </div>
        <?php for ($cell = 0; $cell < $cellCount; $cell++): ?>
            <?php if ($cell % 7 === 0): ?><div class="calendar-row" role="row"><?php endif; ?>
            <?php
            $day = $cell - $startOffset + 1;
            $validDay = $day >= 1 && $day <= $daysInMonth;
            $key = $validDay ? $firstDay->modify('+' . ($day - 1) . ' days')->format('Y-m-d') : null;
            $isToday = $key === $today;
            ?>
            <div class="calendar-day<?= $validDay ? ($isToday ? ' is-today' : '') : ' is-outside' ?>" role="gridcell"<?= $validDay ? ' aria-label="روز ' . $persianDigits($day) . ' ' . $monthNames[$jalaliMonth - 1] . '"' : ' aria-hidden="true"' ?>>
                <?php if ($validDay): ?>
                    <div class="calendar-day-heading">
                        <span class="calendar-day-number"<?= $isToday ? ' aria-current="date"' : '' ?>><?= $persianDigits($day) ?></span>
                        <?php if ($isToday): ?><small class="calendar-today-label">امروز</small><?php endif; ?>
                    </div>
                    <?php foreach (array_slice($admissionsByDay[$key] ?? [], 0, 3) as $admission): ?>
                        <?= Html::a(
                            Html::encode(($admission->patient->first_name ?? '') . ' ' . ($admission->patient->last_name ?? '')),
                            ['view', 'id' => $admission->id],
                            ['class' => 'calendar-event', 'title' => $admission->doctor_name . ' — ' . $admission->ward]
                        ) ?>
                    <?php endforeach; ?>
                    <?php $more = ($dayCounts[$key] ?? 0) - 3; ?>
                    <?php if ($more > 0): ?><?= Html::a('و ' . Yii::$app->formatter->asInteger($more) . ' مورد دیگر', ['index', 'date' => $key], ['class' => 'calendar-more']) ?><?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ($cell % 7 === 6): ?></div><?php endif; ?>
        <?php endfor; ?>
    </div>
</section>
<?php if ($unknownCount > 0): ?>
    <div class="alert alert-info mt-3">تاریخ <?= Yii::$app->formatter->asInteger($unknownCount) ?> پذیرش قدیمی مشخص نیست.
        <?= Html::a('نمایش این پذیرش‌ها', ['index', 'date' => 'unknown'], ['class' => 'alert-link']) ?>
    </div>
<?php endif; ?>
