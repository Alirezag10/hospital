<?php
use yii\helpers\Html;
/** @var string $icon */
/** @var string $message */
/** @var string|null $action */
/** @var array|null $route */
?>
<div class="empty-state">
    <span class="empty-state-icon" aria-hidden="true"><?= $this->render('/layouts/_icon', ['name' => $icon]) ?></span>
    <p><?= Html::encode($message) ?></p>
    <?php if (isset($action, $route)): ?>
        <?= Html::a($action, $route, ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>
