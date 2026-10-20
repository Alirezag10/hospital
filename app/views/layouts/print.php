<?php use yii\helpers\Html; $this->render('_head'); ?>
<?php $this->beginPage() ?>
<!DOCTYPE html><html lang="fa" dir="rtl"><head><title><?= Html::encode($this->title) ?></title><?php $this->head() ?></head>
<body class="clinical-print-body"><?php $this->beginBody() ?>
<main class="clinical-print-page"><?= $content ?></main>
<?php $this->endBody() ?></body></html><?php $this->endPage() ?>
