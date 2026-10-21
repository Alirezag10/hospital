<?php $items=$costItems; ?>
<section class="clinical-costs">
    <h2>هزینهٔ دارو و لوازم مصرفی</h2>
    <?php if ($items === []): ?><p>دارو یا لوازم مصرفی هزینه‌داری ثبت نشده است.</p>
    <?php else: ?><?= $this->render('_item_table', ['items' => $items]) ?><?php endif; ?>
</section>
