<?php
use yii\helpers\Html;
$updatedAt = Yii::$app->formatter->asDatetime(time());
?>
<div class="live-panel-toolbar">
    <div>
        <p class="live-panel-status" data-live-status data-live-updated="<?= Html::encode($updatedAt) ?>" role="status" aria-live="polite">
            آخرین به‌روزرسانی: <bdi><?= Html::encode($updatedAt) ?></bdi>
        </p>
        <small>به‌روزرسانی خودکار هر ۳۰ ثانیه</small>
        <?= Html::a('ورود دوباره', ['/site/login'], ['data-live-login' => '', 'hidden' => true]) ?>
    </div>
    <button type="button" class="btn btn-outline-secondary" data-live-refresh>
        <?= $this->render('_icon', ['name' => 'refresh']) ?>
        <span data-live-button-label>به‌روزرسانی</span>
    </button>
</div>
