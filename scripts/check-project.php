<?php

// Run from the project root: php scripts/check-project.php
// All fixture writes, including nested controller transactions, are rolled back.
if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require dirname(__DIR__) . '/app/vendor/autoload.php';
require dirname(__DIR__) . '/app/vendor/yiisoft/yii2/Yii.php';
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'test');

use app\components\JalaliDate;
use app\models\Admission;
use app\models\AdmissionService;
use app\models\Discharge;
use app\models\Doctor;
use app\models\Patient;
use app\models\Service;
use app\models\Ward;

$config = require dirname(__DIR__) . '/app/config/web.php';
$config['components']['request']['scriptFile'] = dirname(__DIR__) . '/app/web/index.php';
$config['components']['request']['scriptUrl'] = '/index.php';
$config['components']['request']['baseUrl'] = '';
$config['components']['request']['hostInfo'] = 'http://127.0.0.1:8080';
$config['components']['session'] = ['class' => yii\web\Session::class, 'name' => 'hospital-regression', 'useCookies' => false];
$app = new yii\web\Application($config);
$results = [];
$failed = 0;
$transaction = null;

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function check(string $label, callable $callback): void
{
    global $results, $failed;
    try {
        $callback();
        $results[] = 'PASS ' . $label;
    } catch (Throwable $error) {
        $failed++;
        $results[] = 'FAIL ' . $label . ': ' . $error->getMessage();
    }
}

function action(string $route, array $post = [], array $params = []): mixed
{
    global $app;
    $_SERVER['REQUEST_METHOD'] = $post === [] ? 'GET' : 'POST';
    $request = new yii\web\Request([
        'scriptFile' => dirname(__DIR__) . '/app/web/index.php',
        'scriptUrl' => '/index.php', 'baseUrl' => '',
        'hostInfo' => 'http://127.0.0.1:8080',
        // Direct controller tests; CSRF protection is tested separately over HTTP.
        'enableCsrfValidation' => false,
    ]);
    $request->setBodyParams($post);
    $request->setQueryParams($params);
    $request->setUrl('/' . $route . ($params === [] ? '' : '?' . http_build_query($params)));
    $app->set('request', $request);
    $app->set('response', new yii\web\Response());
    $app->set('view', new yii\web\View());
    return $app->runAction($route, $params);
}

function nationalCode(): string
{
    do {
        $prefix = '0' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $prefix[$i] * (10 - $i);
        }
        $rest = $sum % 11;
        $code = $prefix . ($rest < 2 ? $rest : 11 - $rest);
    } while (Patient::find()->where(['national_code' => $code])->exists());
    return $code;
}

try {
    $app->db->open();
    $tables = ['patients', 'doctors', 'wards', 'admissions', 'services', 'admission_services', 'discharges'];
    $before = [];
    foreach ($tables as $table) {
        $engine = $app->db->createCommand('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table', [':table' => $table])->queryScalar();
        expect($engine === 'InnoDB', 'Missing transactional table: ' . $table);
        $before[$table] = (int) $app->db->createCommand('SELECT COUNT(*) FROM [[' . $table . ']]')->queryScalar();
    }
    check('Existing patients/doctors/wards/services satisfy validation rules', function () use ($app) {
        foreach ([Patient::class, Doctor::class, Ward::class, Service::class] as $class) {
            foreach ($class::find()->each(100) as $record) {
                if ($record instanceof Patient) {
                    $record->birthDateInput = $record->birth_date === null ? null : $app->formatter->asDate($record->birth_date);
                }
                expect($record->validate(), 'Existing ' . $record->tableName() . ' record has invalid fields: ' . implode(', ', array_keys($record->errors)));
            }
        }
    });
    check('Existing admission statuses, links and discharge totals are consistent', function () use ($app) {
        $invalid = $app->db->createCommand("SELECT COUNT(*) FROM admissions a LEFT JOIN patients p ON p.id=a.patient_id LEFT JOIN doctors doc ON doc.id=a.doctor_id LEFT JOIN wards w ON w.id=a.ward_id LEFT JOIN discharges d ON d.admission_id=a.id WHERE p.id IS NULL OR doc.id IS NULL OR w.id IS NULL OR a.status NOT IN ('admitted','discharged') OR (a.status='admitted' AND d.id IS NOT NULL) OR (a.status='discharged' AND d.id IS NULL)")->queryScalar();
        expect((int) $invalid === 0, 'Existing admission status/relation mismatch');
        $totals = $app->db->createCommand('SELECT d.id FROM discharges d LEFT JOIN admission_services s ON s.admission_id=d.admission_id GROUP BY d.id,d.total_amount HAVING d.total_amount <> COALESCE(SUM(s.quantity*s.unit_price),0)')->queryAll();
        expect($totals === [], 'Existing discharge total differs from recorded services');
    });
    $transaction = $app->db->beginTransaction();
    $tag = 'Regression_' . bin2hex(random_bytes(5));
    $code = nationalCode();
    $persian = strtr($code, array_combine(str_split('0123456789'), preg_split('//u', '۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY)));

    check('Patient registration, Persian digits, Iranian mobile, Jalali date', function () use ($code, $persian, $tag) {
        $response = action('patient/create', ['Patient' => ['first_name' => $tag, 'last_name' => '<script>alert(1)</script>', 'national_code' => $persian, 'mobile' => '+۹۸۹۱۲۳۴۵۶۷۸۹', 'birthDateInput' => '۱۴۰۳/۰۱/۰۱', 'birth_date' => '2099-01-01']]);
        $patient = Patient::findOne(['national_code' => $code]);
        expect($response instanceof yii\web\Response && $response->statusCode === 302, 'Registration did not redirect');
        expect($patient !== null && $patient->mobile === '09123456789' && $patient->birth_date === '2024-03-20', 'Normalization/conversion failed');
        expect(str_starts_with($patient->national_code, '0'), 'Leading zero was lost');
    });
    $patient = Patient::findOne(['national_code' => $code]);
    expect($patient !== null, 'Fixture patient could not be created');

    check('Invalid national codes and mobile numbers are rejected', function () use ($tag) {
        foreach (['1111111111', '0123456780', ['bad']] as $value) {
            $m = new Patient(['first_name' => $tag, 'last_name' => 'test', 'national_code' => $value, 'mobile' => '09123456789']);
            expect(!$m->validate() && $m->hasErrors('national_code'), 'Invalid national code accepted');
        }
        foreach (['08123456789', '0912', '09979999999', ['bad']] as $value) {
            $m = new Patient(['first_name' => $tag, 'last_name' => 'test', 'national_code' => nationalCode(), 'mobile' => $value]);
            expect(!$m->validate() && $m->hasErrors('mobile'), 'Invalid mobile accepted');
        }
        expect(Patient::normalizeMobile('0098 (912) 345-6789') === '09123456789', 'International normalization failed');
    });
    check('Duplicate national code is rejected', function () use ($patient) {
        $m = new Patient(['first_name' => 'test', 'last_name' => 'test', 'national_code' => $patient->national_code, 'mobile' => $patient->mobile]);
        expect(!$m->validate() && $m->hasErrors('national_code'), 'Duplicate code accepted');
    });
    check('Jalali invalid days and future birth dates are rejected', function () {
        expect(JalaliDate::parse('1403/01/01') === '2024-03-20', 'Known date mismatch');
        expect(JalaliDate::parse('1402/12/30') === null, 'Non-leap Esfand accepted');
        expect(JalaliDate::parse('1403/07/31') === null, 'Invalid 31st accepted');
        $m = new Patient(['first_name' => 'test', 'last_name' => 'test', 'national_code' => nationalCode(), 'mobile' => '09123456789', 'birthDateInput' => '1500/01/01']);
        expect(!$m->validate() && $m->hasErrors('birthDateInput'), 'Future date accepted');
    });
    action('doctor/create', ['Doctor' => ['name' => $tag, 'specialty' => 'test', 'mobile' => '09123456789']]);
    action('ward/create', ['Ward' => ['name' => $tag, 'floor' => '1']]);
    $doctor = Doctor::findOne(['name' => $tag]);
    $ward = Ward::findOne(['name' => $tag]);
    expect($doctor !== null && $ward !== null, 'Doctor/ward fixtures failed');
    $service = new Service(['title' => $tag, 'price' => 120000]);
    expect($service->save(), 'Service fixture failed');
    $other = new Patient(['first_name' => $tag . '_other', 'last_name' => 'test', 'national_code' => nationalCode(), 'mobile' => '09123456789']);
    expect($other->save(), 'Second patient fixture failed');
    check('Ward/doctor/service uniqueness and title trimming', function () use ($tag) {
        foreach ([new Ward(['name' => $tag]), new Doctor(['name' => $tag, 'specialty' => 'test']), new Service(['title' => ' ' . $tag . ' ', 'price' => 1])] as $m) {
            expect(!$m->validate(), 'Duplicate entity accepted');
        }
    });
    check('Admission registration ignores protected fields and restores locked patient', function () use ($patient, $other, $doctor, $ward) {
        $response = action('admission/create', ['Admission' => ['patient_id' => $other->id, 'doctor_id' => $doctor->id, 'ward_id' => $ward->id, 'doctor_name' => 'tampered', 'ward' => 'tampered', 'status' => 'discharged', 'admission_date' => '2099-01-01']], ['patient_id' => $patient->id]);
        $m = Admission::findOne(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
        expect($response instanceof yii\web\Response && $m !== null, 'Admission registration failed');
        expect($m->status === 'admitted' && $m->doctor_name === $doctor->name && $m->ward === $ward->name, 'Protected fields were changed');
        expect(!str_starts_with($m->admission_date, '2099'), 'Admission date was taken from request');
    });
    $admission = Admission::findOne(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);
    expect($admission !== null, 'Admission fixture missing');
    check('Nonexistent related IDs are rejected', function () {
        $m = new Admission(['patient_id' => 4294967295, 'doctor_id' => 4294967295, 'ward_id' => 4294967295]);
        expect(!$m->validate(), 'Nonexistent relation accepted');
    });
    check('Service registration takes tariff and admission from server', function () use ($admission, $service) {
        $response = action('admission-service/create', ['AdmissionService' => ['admission_id' => 4294967295, 'service_id' => $service->id, 'quantity' => 2, 'unit_price' => 1]], ['admission_id' => $admission->id]);
        $item = AdmissionService::findOne(['admission_id' => $admission->id, 'service_id' => $service->id]);
        expect($response instanceof yii\web\Response && $item !== null && (int) $item->unit_price === 120000, 'Server tariff/locked admission failed');
    });
    check('Invalid service quantities are rejected', function () use ($admission, $service) {
        foreach ([0, -1, '1.5', '999999999999999999999'] as $quantity) {
            $count = AdmissionService::find()->where(['admission_id' => $admission->id])->count();
            $response = action('admission-service/create', ['AdmissionService' => ['admission_id' => $admission->id, 'service_id' => $service->id, 'quantity' => $quantity]]);
            expect(is_string($response) && AdmissionService::find()->where(['admission_id' => $admission->id])->count() === $count, 'Invalid quantity was stored');
        }
    });
    check('Detail HTML escapes patient names and shows services', function () use ($admission) {
        $html = action('admission/view', [], ['id' => $admission->id]);
        expect(str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($html, '<script>alert(1)</script>'), 'Patient name was not escaped');
        expect(str_contains($html, 'جمع خدمات فعلی'), 'Service details missing');
    });
    check('Discharge uses server total/date and locked admission', function () use ($admission) {
        $response = action('discharge/create', ['Discharge' => ['admission_id' => 4294967295, 'description' => 'test', 'total_amount' => 1, 'discharge_date' => '2099-01-01']], ['admission_id' => $admission->id]);
        $m = Discharge::findOne(['admission_id' => $admission->id]);
        expect($response instanceof yii\web\Response && $m !== null && (int) $m->total_amount === 240000, 'Discharge total failed');
        expect(!str_starts_with($m->discharge_date, '2099') && Admission::findOne($admission->id)->status === 'discharged', 'Date/status incorrect');
    });
    check('Repeated discharge is rejected', function () use ($admission) {
        $html = action('discharge/create', ['Discharge' => ['admission_id' => $admission->id]], ['admission_id' => $admission->id]);
        expect(is_string($html) && Discharge::find()->where(['admission_id' => $admission->id])->count() == 1, 'Duplicate discharge was stored');
    });
    check('No service can be added after discharge', function () use ($admission, $service) {
        $count = AdmissionService::find()->where(['admission_id' => $admission->id])->count();
        $html = action('admission-service/create', ['AdmissionService' => ['admission_id' => $admission->id, 'service_id' => $service->id, 'quantity' => 1]]);
        expect(is_string($html) && AdmissionService::find()->where(['admission_id' => $admission->id])->count() === $count, 'Closed admission accepted a service');
    });
    check('AJAX endpoints return selected records and reject array terms', function () use ($tag, $patient, $doctor) {
        $patients = action('admission/search-patients', [], ['q' => $tag]);
        $doctors = action('admission/search-doctors', [], ['q' => strtolower($tag)]);
        expect(in_array((int) $patient->id, array_column($patients, 'id'), true), 'Patient search missing record');
        expect(in_array((int) $doctor->id, array_column($doctors, 'id'), true), 'Case-insensitive doctor search failed');
        expect(action('admission/search-patients', [], ['q' => ['x']]) === [], 'Array query accepted');
        expect(count($patients) <= 10, 'AJAX result cap failed');
    });
    check('Calendar and patient detail render', function () use ($patient) {
        expect(str_contains(action('admission/calendar'), 'calendar-grid'), 'Calendar missing');
        expect(str_contains(action('patient/view', [], ['id' => $patient->id]), '&lt;script&gt;'), 'Patient detail did not escape HTML');
    });
    check('Array filters render errors instead of crashing', function () {
        foreach (['patient' => 'PatientSearch', 'admission' => 'AdmissionSearch'] as $controller => $form) {
            foreach (['name', 'national_code', 'mobile'] as $attribute) {
                expect(is_string(action($controller . '/index', [], [$form => [$attribute => ['x']]])), 'Invalid filter failed');
            }
        }
    });
    check('Mobile-only admission filter has correct empty state', function () {
        $html = action('admission/index', [], ['AdmissionSearch' => ['mobile' => '00000000000']]);
        expect(str_contains($html, 'پذیرشی مطابق جست'), 'Mobile filter not recognized');
    });
    check('Malformed POST fields and form scopes return 400', function () {
        $forms = ['patient' => ['Patient', 'first_name'], 'doctor' => ['Doctor', 'name'], 'ward' => ['Ward', 'name'], 'admission' => ['Admission', 'patient_id'], 'admission-service' => ['AdmissionService', 'quantity'], 'discharge' => ['Discharge', 'description']];
        foreach ($forms as $route => [$name, $field]) {
            foreach ([[$name => [$field => ['x']]], [$name => 'x']] as $post) {
                try {
                    action($route . '/create', $post);
                    throw new RuntimeException('Malformed form accepted');
                } catch (yii\web\BadRequestHttpException $error) {
                    expect($error->statusCode === 400, 'Wrong status for malformed form');
                }
            }
        }
    });
} catch (Throwable $error) {
    $failed++;
    $results[] = 'FAIL setup/runtime: ' . $error->getMessage();
} finally {
    if ($transaction !== null && $transaction->isActive) {
        $transaction->rollBack();
    }
    if (isset($before)) {
        foreach ($before as $table => $count) {
            if ((int) $app->db->createCommand('SELECT COUNT(*) FROM [[' . $table . ']]')->queryScalar() !== $count) {
                $failed++;
                $results[] = 'FAIL rollback count: ' . $table;
            }
        }
    }
    $app->session->close();
}
echo implode(PHP_EOL, $results), PHP_EOL;
echo count($results), ' checks; ', $failed, ' failures. Fixtures rolled back.', PHP_EOL;
exit($failed === 0 ? 0 : 1);
