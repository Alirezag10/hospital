<?php
// Isolated query-budget regression test; does not render or modify the real application.
declare(strict_types=1);
$dsn=getenv('HOSPITAL_TEST_DSN') ?: '';
if (!preg_match('/dbname=[a-zA-Z0-9_]+_test(?:;|$)/',$dsn)) { throw new RuntimeException('Use an isolated _test database.'); }
require __DIR__.'/../app/vendor/autoload.php';
require __DIR__.'/../app/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'calendar-test','basePath'=>__DIR__.'/../app','timeZone'=>'Asia/Tehran','components'=>['db'=>[
    'class'=>yii\db\Connection::class,'dsn'=>$dsn,'username'=>getenv('HOSPITAL_TEST_USER') ?: 'root',
    'password'=>getenv('HOSPITAL_TEST_PASSWORD') ?: '','charset'=>'utf8mb4',
]]]);
class CalendarProbe extends app\controllers\AdmissionController {
    public function render($view,$params=[]) { return $params; }
}
$db=Yii::$app->db;
$source=$db->createCommand('SELECT * FROM admissions ORDER BY id LIMIT 1')->queryOne();
if (!$source) { throw new RuntimeException('Run staff_workflow.php first.'); }
$tx=$db->beginTransaction();
try {
    $first=app\components\JalaliDate::toGregorian(1405,1,1);
    for ($day=0;$day<15;$day++) {
        for ($item=0;$item<4;$item++) {
            $db->createCommand()->insert('admissions',[
                'patient_id'=>$source['patient_id'],'doctor_id'=>$source['doctor_id'],'ward_id'=>$source['ward_id'],
                'ward'=>$source['ward'],'doctor_name'=>$source['doctor_name'],'status'=>'discharged',
                'admission_date'=>$first->modify('+'.$day.' days')->format('Y-m-d').' 10:0'.$item.':00',
            ])->execute();
        }
    }
    $probe=new CalendarProbe('admission',Yii::$app);
    // Warm schema metadata; only application SELECTs should consume the query budget.
    $probe->actionCalendar('1405-01');
    $logger=Yii::getLogger();$logger->flushInterval=0;
    $before=count($logger->getProfiling(['yii\db\Command::query']));
    $calendar=$probe->actionCalendar('1405-01');
    $queries=count($logger->getProfiling(['yii\db\Command::query']))-$before;
    if ($queries>5 || count($calendar['dayCounts'])!==15) { throw new RuntimeException('Calendar query budget or daily counts failed: '.$queries); }
    foreach ($calendar['admissionsByDay'] as $day=>$rows) {
        if (count($rows)!==3 || $calendar['dayCounts'][$day]!==4) { throw new RuntimeException('Daily previews or counts incorrect.'); }
        $times=array_map(static fn($r)=>substr($r->admission_date,-8),$rows);
        if ($times!==['10:00:00','10:01:00','10:02:00']) { throw new RuntimeException('Preview ordering changed.'); }
    }
    echo "PASS: 15 occupied days, 4 admissions/day, 3 ordered previews/day, $queries SELECT queries.\n";
} finally { $tx->rollBack(); }
