<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var yii\web\View $this */
?>
<footer id="footer" class="mt-auto py-3 bg-body-tertiary" dir="rtl">
    <div class="container">
        <div class="footer-content d-flex flex-wrap justify-content-between align-items-center gap-3 text-body-secondary small">
            <span>&copy; <?= Html::encode(Yii::$app->name) ?> — <?= Html::encode(Yii::$app->formatter->asDate(time(), 'yyyy')) ?></span>
        </div>
    </div>
</footer>
