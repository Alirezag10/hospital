<?php
use yii\helpers\Html;
/** @var app\models\Patient|null $patient */
?>
<span class="patient-label">
    <strong><?= Html::encode($patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص') ?></strong>
    <?php if ($patient): ?><small>کد ملی: <bdi><?= Html::encode($patient->national_code) ?></bdi></small><?php endif; ?>
</span>
