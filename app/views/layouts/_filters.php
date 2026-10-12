<?php
use yii\helpers\Html;
/** @var array $filters Attribute => [label, value] */
/** @var string $formName */
?>
<div class="applied-filters" aria-label="فیلترهای اعمال‌شده">
<?php foreach ($filters as $attribute => [$label, $value]): ?>
    <?php
    if (!is_scalar($value) || (string) $value === '') { continue; }
    $params = Yii::$app->request->queryParams;
    unset($params['page'], $params['r']);
    if ($formName !== '') {
        unset($params[$formName][$attribute]);
    } else {
        unset($params[$attribute]);
    }
    ?>
    <?= Html::a(Html::encode($label . ': ' . $value) . '<span aria-hidden="true">×</span>',
        array_merge(['index'], $params), ['class' => 'filter-chip', 'aria-label' => 'حذف فیلتر ' . $label]) ?>
<?php endforeach; ?>
</div>
