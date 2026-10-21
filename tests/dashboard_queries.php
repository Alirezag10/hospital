<?php

// Uses only a separate *_test database; all fixtures are rolled back.
declare(strict_types=1);

use app\components\DashboardData;
use app\models\User;

$dsn = getenv('HOSPITAL_TEST_DSN') ?: '';
if (!preg_match('/dbname=[a-zA-Z0-9_]+_test(?:;|$)/', $dsn)) {
    throw new RuntimeException('Use an isolated _test database.');
}

require __DIR__ . '/../app/vendor/autoload.php';
require __DIR__ . '/../app/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../app/config/web.php';
new yii\console\Application([
    'id' => 'dashboard-test',
    'basePath' => __DIR__ . '/../app',
    'timeZone' => 'Asia/Tehran',
    'components' => [
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => $dsn,
            'username' => getenv('HOSPITAL_TEST_USER') ?: 'root',
            'password' => getenv('HOSPITAL_TEST_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
        ],
        'formatter' => $config['components']['formatter'],
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => User::class,
            'enableSession' => false,
        ],
    ],
]);

function dashboardCheck(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    echo 'PASS ', $message, PHP_EOL;
}

$db = Yii::$app->db;
$doctors = User::find()->where(['role' => User::DOCTOR, 'is_active' => 1])->limit(2)->all();
if (count($doctors) !== 2) {
    throw new RuntimeException('Run staff_workflow.php first.');
}

$transaction = $db->beginTransaction();
try {
    $createCase = static function (int $number, int $doctorId, ?string $ready = null, string $status = 'admitted') use ($db): int {
        $db->createCommand()->insert('patients', [
            'first_name' => 'بیمار داشبورد',
            'last_name' => (string) $number,
            'national_code' => sprintf('99%08d', $number),
            'mobile' => '09121234567',
        ])->execute();
        $patientId = (int) $db->getLastInsertID();
        $db->createCommand()->insert('admissions', [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'ward_id' => 1,
            'doctor_name' => 'پزشک داشبورد',
            'ward' => 'بخش داشبورد',
            'admission_date' => '2000-01-01 09:00:00',
            'ready_at' => $ready,
            'status' => $status,
        ])->execute();
        return (int) $db->getLastInsertID();
    };

    for ($number = 1; $number <= 12; $number++) {
        $createCase($number, (int) $doctors[0]->doctorId);
    }
    $foreignCase = $createCase(13, (int) $doctors[1]->doctorId);
    $readyFirst = $createCase(14, (int) $doctors[0]->doctorId, '2001-01-01 09:00:00');
    $readySecond = $createCase(15, (int) $doctors[1]->doctorId, '2001-01-02 09:00:00');
    $closedCase = $createCase(16, (int) $doctors[0]->doctorId, '2000-01-01 09:00:00', 'discharged');

    Yii::$app->user->setIdentity($doctors[0]);
    $data = DashboardData::load();
    dashboardCheck(count($data['admissions']) === 8 && $data['total'] > 8, 'preview stays bounded and reports the full count');
    foreach ($data['admissions'] as $admission) {
        dashboardCheck((int) $admission->doctor_id === (int) $doctors[0]->doctorId
            && $admission->status === 'admitted', 'doctor preview contains only assigned active cases');
    }
    dashboardCheck(!in_array($foreignCase, array_column($data['admissions'], 'id'))
        && !in_array($closedCase, array_column($data['admissions'], 'id')), 'foreign and closed cases stay outside the doctor worklist');

    Yii::$app->user->setIdentity(new User(['id' => 999999, 'role' => User::DOCTOR]));
    $data = DashboardData::load();
    dashboardCheck($data['admissions'] === [] && $data['total'] === 0, 'doctor without a profile sees an empty scoped dashboard');

    $dischargeUser = User::find()->where(['role' => User::DISCHARGE, 'is_active' => 1])->one();
    Yii::$app->user->setIdentity($dischargeUser);
    $data = DashboardData::load();
    dashboardCheck(array_map('intval', array_column(array_slice($data['admissions'], 0, 2), 'id')) === [$readyFirst, $readySecond], 'discharge queue puts the longest wait first');
    foreach ($data['admissions'] as $admission) {
        dashboardCheck($admission->status === 'admitted' && $admission->ready_at !== null, 'queue contains only active medically approved cases');
    }

    $secretary = User::find()->where(['role' => User::SECRETARY, 'is_active' => 1])->one();
    Yii::$app->user->setIdentity($secretary);
    $data = DashboardData::load();
    dashboardCheck((int) $data['admissions'][0]->id === $closedCase, 'recent admissions are ordered newest first');

    $logger = Yii::getLogger();
    $logger->flushInterval = 0;
    foreach ([User::ADMIN, User::SECRETARY, User::DOCTOR, User::DISCHARGE] as $role) {
        Yii::$app->user->setIdentity(User::find()->where(['role' => $role, 'is_active' => 1])->one());
        DashboardData::load(); // Warm schema metadata before measuring application queries.
        $before = count($logger->getProfiling(['yii\db\Command::query']));
        $data = DashboardData::load();
        foreach ($data['admissions'] as $admission) {
            $patient = $admission->patient;
        }
        $queries = count($logger->getProfiling(['yii\db\Command::query'])) - $before;
        dashboardCheck($queries > 0 && $queries <= 8, $role . ' dashboard uses at most 8 SELECTs including patient data (' . $queries . ')');
    }

    $now = new DateTimeImmutable('2026-10-21 12:00:00', new DateTimeZone('Asia/Tehran'));
    dashboardCheck(DashboardData::waitingTime('2026-10-21 11:59:00', $now) === '۱ دقیقه', 'waiting time uses Persian digits');
    dashboardCheck(DashboardData::waitingTime('2026-10-20 09:55:00', $now) === '۱ روز و ۲ ساعت و ۵ دقیقه', 'waiting time handles day boundaries');
    dashboardCheck(DashboardData::waitingTime('2026-10-22 12:00:00', $now) === 'کمتر از یک دقیقه', 'future timestamps never produce negative waiting time');
} finally {
    $transaction->rollBack();
}
