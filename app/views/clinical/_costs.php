<?php
$prescriptions = $admission->getPrescriptions()->with('items')->orderBy(['id' => SORT_ASC])->all();
$items = [];
foreach ($prescriptions as $prescription) { array_push($items, ...$prescription->items); }
?>
<section class="clinical-costs">
    <h2>هزینهٔ دارو و لوازم مصرفی</h2>
    <?php if ($items === []): ?><p>دارو یا لوازم مصرفی هزینه‌داری ثبت نشده است.</p>
    <?php else: ?><?= $this->render('_item_table', ['items' => $items]) ?><?php endif; ?>
</section>
