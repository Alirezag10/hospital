<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var yii\web\View $this */
?>
<footer id="footer" class="mt-auto py-3 bg-body-tertiary" dir="rtl">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 text-body-secondary small">
            <span>&copy; <?= Html::encode(Yii::$app->name) ?> — <span data-shamsi-year="<?= date('Y-m-d') ?>"><?= date('Y') ?></span></span>
            <span>سامانه پذیرش، خدمات و ترخیص بیمارستان</span>
        </div>
    </div>
</footer>
