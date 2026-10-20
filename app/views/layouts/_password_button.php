<?php
use yii\helpers\Html;
$icons = Html::tag('span', $this->render('_icon', ['name' => 'eye']), ['class' => 'password-eye'])
    . Html::tag('span', $this->render('_icon', ['name' => 'eye-off']), ['class' => 'password-eye-off']);
?>
<?= Html::button($icons, ['class' => 'password-toggle', 'type' => 'button', 'data-password-toggle' => $inputId,
    'aria-controls' => $inputId, 'aria-pressed' => 'false', 'aria-label' => 'نمایش رمز عبور', 'title' => 'نمایش رمز عبور']) ?>
