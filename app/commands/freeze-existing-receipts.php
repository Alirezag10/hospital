<?php
// Run once after upgrade_workflow_and_receipts.sql; safe to repeat.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application([
    'id'=>'hospital-freeze-receipts', 'name'=>'سامانه بیمارستان', 'basePath'=>dirname(__DIR__),
    'components'=>['db'=>require dirname(__DIR__).'/config/db.php'],
]);
$count = 0;
foreach (app\models\Discharge::find()->where(['receipt_data'=>null])->select('id')->column() as $id) {
    $tx = Yii::$app->db->beginTransaction();
    try {
        $discharge = app\models\Discharge::findBySql('SELECT * FROM discharges WHERE id=:id FOR UPDATE',[':id'=>$id])->one();
        if ($discharge->receipt_data === null) {
            $admission = app\models\Admission::findOne($discharge->admission_id);
            $snapshot = app\components\ReceiptSnapshot::capture($admission,$discharge);
            app\models\Discharge::updateAll(['receipt_data'=>$snapshot],['id'=>$id]);
            $count++;
        }
        $tx->commit();
    } catch (Throwable $error) {
        if ($tx->isActive) { $tx->rollBack(); }
        throw $error;
    }
}
echo "Frozen existing receipts: $count. Historical amounts were not changed.\n";
