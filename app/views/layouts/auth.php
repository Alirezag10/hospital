<?php
use yii\helpers\Html;
$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html><html lang="fa" dir="rtl" class="auth-document"><head><title><?= Html::encode($this->title) ?></title><?php $this->head() ?></head>
<body class="auth-page"><?php $this->beginBody() ?>
<a class="skip-link" href="#main">رفتن به فرم ورود</a>
<header class="auth-header"><div class="auth-header-inner">
    <?= Html::a(Html::tag('span', $this->render('_icon', ['name' => 'ward']), ['class' => 'brand-mark', 'aria-hidden' => 'true']) . Html::tag('span', Html::encode(Yii::$app->name)), ['/site/login'], ['class' => 'auth-brand']) ?>
    <span>پنل کارکنان</span>
</div></header>
<main id="main" class="auth-main" tabindex="-1"><?= $content ?></main>
<footer class="auth-footer">© <?= Html::encode(Yii::$app->name) ?> · <?= Html::encode(Yii::$app->formatter->asDate(time(), 'yyyy')) ?></footer>
<?php $this->endBody() ?></body></html><?php $this->endPage() ?>
