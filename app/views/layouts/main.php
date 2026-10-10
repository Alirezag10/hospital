<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;

$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" dir="rtl">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<a class="skip-link" href="#main">رفتن به محتوای اصلی</a>
<?= $this->render('_header') ?>

<main id="main" class="flex-grow-1" role="main" tabindex="-1">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?php if ($message = Yii::$app->session->getFlash('success')): ?>
            <div class="alert alert-success app-flash" role="status">
                <?= Html::encode($message) ?>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</main>

<?= $this->render('_footer') ?>

<dialog id="app-confirm-dialog" class="app-confirm-dialog" aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message" dir="rtl">
    <div class="app-confirm-dialog__content">
        <h2 id="app-confirm-title">تأیید عملیات</h2>
        <p id="app-confirm-message"></p>
        <div class="app-confirm-dialog__actions">
            <button type="button" class="btn btn-outline-secondary" data-confirm-cancel>انصراف</button>
            <button type="button" class="btn btn-primary" data-confirm-accept>تأیید</button>
        </div>
    </div>
</dialog>

<?php if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)): ?>
    <?php $debugToolbarEnabled = defined('YII_DEBUG') && YII_DEBUG && YII_ENV_DEV; ?>
    <?= Html::button(
        $debugToolbarEnabled ? 'دیباگ: روشن' : 'دیباگ: خاموش',
        [
            'class' => 'debug-toolbar-toggle',
            'data-debug-toolbar-toggle' => '1',
            'data-debug-toolbar-enabled' => $debugToolbarEnabled ? '1' : '0',
            'aria-pressed' => $debugToolbarEnabled ? 'true' : 'false',
            'type' => 'button',
        ],
    ) ?>
<?php endif ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
