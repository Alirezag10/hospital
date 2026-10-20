<?php use yii\helpers\Html; ?>
<section class="clinical-records" aria-labelledby="clinical-records-title">
    <h2 id="clinical-records-title">نسخه‌ها و دستورات درمانی</h2>
    <?php if ($prescriptions === []): ?><p class="clinical-empty">هنوز نسخه یا دستوری ثبت نشده است.</p><?php endif; ?>
    <?php foreach ($prescriptions as $prescription): ?>
        <article class="clinical-record" id="prescription-<?= (int) $prescription->id ?>">
            <header><h3>نسخه <?= Yii::$app->formatter->asInteger($prescription->id) ?></h3><span><?= Html::encode($prescription->doctor->name) ?> · <time><?= Html::encode(Yii::$app->formatter->asDatetime($prescription->created_at)) ?></time></span></header>
            <?php if ($prescription->author && (int) $prescription->author->doctor_id !== (int) $prescription->doctor_id): ?><p class="clinical-empty">ثبت‌کننده: <?= Html::encode($prescription->author->full_name) ?></p><?php endif; ?>
            <h4>تشخیص و شرح وضعیت</h4><p class="clinical-prose"><?= Html::encode($prescription->diagnosis) ?></p>
            <?php if ($prescription->notes): ?><h4>دستورات پزشک</h4><p class="clinical-prose"><?= Html::encode($prescription->notes) ?></p><?php endif; ?>
            <?php if ($prescription->items): ?><?= $this->render('_item_table', ['items' => $prescription->items, 'showInstructions' => true]) ?><?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
