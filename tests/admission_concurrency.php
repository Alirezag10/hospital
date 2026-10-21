<?php
// Isolated integration test: two real PHP processes try to admit the same patient.
declare(strict_types=1);
$dsn=getenv('HOSPITAL_TEST_DSN') ?: '';
if (!preg_match('/dbname=[a-zA-Z0-9_]+_test(?:;|$)/',$dsn)) { throw new RuntimeException('Use an isolated _test database.'); }
require __DIR__.'/../app/vendor/autoload.php';
require __DIR__.'/../app/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'concurrency-test','basePath'=>__DIR__.'/../app','components'=>['db'=>[
    'class'=>yii\db\Connection::class,'dsn'=>$dsn,'username'=>getenv('HOSPITAL_TEST_USER') ?: 'root',
    'password'=>getenv('HOSPITAL_TEST_PASSWORD') ?: '','charset'=>'utf8mb4',
]]]);
$db=Yii::$app->db;
if (($argv[1] ?? '') === 'worker') {
    $model=new app\models\Admission(['patient_id'=>(int)$argv[2],'doctor_id'=>(int)$argv[3],'ward_id'=>(int)$argv[4]]);
    $saved=app\components\AdmissionRegistration::save($model);
    echo json_encode(['saved'=>$saved,'errors'=>$model->getErrors()]);
    exit;
}
$doctor=(int)$db->createCommand('SELECT MIN(id) FROM doctors')->queryScalar();
$ward=(int)$db->createCommand('SELECT MIN(id) FROM wards')->queryScalar();
if (!$doctor || !$ward) { throw new RuntimeException('Run staff_workflow.php first.'); }
$db->createCommand()->insert('patients',['first_name'=>'تست','last_name'=>'هم‌زمانی','national_code'=>'9999999989','mobile'=>'09121234567'])->execute();
$patient=(int)$db->getLastInsertID();
$processes=[];
try {
    $tx=$db->beginTransaction();
    $db->createCommand('SELECT id FROM patients WHERE id=:id FOR UPDATE',[':id'=>$patient])->queryScalar();
    for ($i=0;$i<2;$i++) {
        $process=proc_open([PHP_BINARY,__FILE__,'worker',(string)$patient,(string)$doctor,(string)$ward],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
        if (!is_resource($process)) { throw new RuntimeException('Cannot start concurrent worker.'); }
        $processes[]=[$process,$pipes];
    }
    usleep(250000);
    $tx->commit();
    $results=[];
    foreach ($processes as [$process,$pipes]) {
        $output=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);
        fclose($pipes[1]);fclose($pipes[2]);
        if (proc_close($process)!==0) { throw new RuntimeException($errors ?: $output); }
        $results[]=json_decode($output,true,512,JSON_THROW_ON_ERROR);
    }
    $saved=count(array_filter($results,static fn($r)=>$r['saved']));
    $count=(int)$db->createCommand('SELECT COUNT(*) FROM admissions WHERE patient_id=:id',[':id'=>$patient])->queryScalar();
    if ($saved!==1 || $count!==1) { throw new RuntimeException('Concurrent admission protection failed.'); }
    echo "PASS: two processes, one accepted admission, one validation rejection.\n";
} finally {
    if (isset($tx) && $tx->isActive) { $tx->rollBack(); }
    $db->createCommand()->delete('admissions',['patient_id'=>$patient])->execute();
    $db->createCommand()->delete('patients',['id'=>$patient])->execute();
}
