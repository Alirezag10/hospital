<?php
// Local, fictional demonstration data; never expose this file under app/web.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

new yii\console\Application([
    'id' => 'hospital-staff-demo', 'basePath' => dirname(__DIR__),
    'components' => ['db' => require dirname(__DIR__) . '/config/db.php'],
]);

use app\models\User;
use app\models\UserForm;
use app\models\Prescription;
use app\models\PrescriptionItem;

$credentialsPath = dirname(__DIR__) . '/runtime/demo-staff-access.txt';
if (!is_writable(dirname($credentialsPath)) || (is_file($credentialsPath) && !is_writable($credentialsPath))) {
    throw new RuntimeException('The local credential file must be writable before adding accounts.');
}
$db = Yii::$app->db;
$transaction = $db->beginTransaction();
$added = ['users' => 0, 'prescriptions' => 0, 'prescription_items' => 0];
$credentialLines = [];

function demoUser(string $username, string $name, string $role, ?int $doctorId = null): User
{
    global $added, $credentialLines;
    $existing = $doctorId === null ? User::findOne(['username' => $username]) : User::findOne(['doctor_id' => $doctorId]);
    if ($existing !== null) {
        if ($existing->role !== $role || (int) $existing->is_active !== 1) {
            throw new RuntimeException('An existing account conflicts with the demo. No account will be overwritten.');
        }
        return $existing;
    }
    $password = Yii::$app->security->generateRandomString(16);
    $form = new UserForm([
        'username' => $username, 'full_name' => $name, 'role' => $role, 'doctor_id' => $doctorId,
        'password' => $password, 'password_repeat' => $password, 'is_active' => 1,
    ]);
    $user = $form->save();
    if ($user === null) { throw new RuntimeException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE)); }
    $added['users']++;
    $credentialLines[] = "Username: {$username}\nRole: {$user->roleLabel}\nPassword: {$password}\n";
    return $user;
}

try {
    $admissions = $db->createCommand(
        "SELECT a.id, a.doctor_id, d.name AS doctor_name FROM admissions a
         INNER JOIN doctors d ON d.id = a.doctor_id
         WHERE a.status = 'admitted' AND NOT EXISTS (SELECT 1 FROM discharges x WHERE x.admission_id = a.id)
         ORDER BY a.id LIMIT 4 FOR UPDATE"
    )->queryAll();
    if ($admissions === []) { throw new RuntimeException('No open admission exists for demonstration prescriptions.'); }

    demoUser('demo.secretary', 'سارا احمدی (نمونه)', User::SECRETARY);
    demoUser('demo.discharge', 'رضا کریمی (نمونه)', User::DISCHARGE);
    foreach ($admissions as $admission) {
        $doctorId = (int) $admission['doctor_id'];
        $doctorUser = demoUser('demo.doctor.' . $doctorId, $admission['doctor_name'], User::DOCTOR, $doctorId);
        $diagnosis = 'نسخه نمایشی برای پذیرش ' . $admission['id'] . '؛ صرفاً نمونه آموزشی.';
        if (Prescription::find()->where(['admission_id' => $admission['id'], 'diagnosis' => $diagnosis])->exists()) { continue; }
        $prescription = new Prescription([
            'admission_id' => $admission['id'], 'doctor_id' => $doctorId, 'user_id' => $doctorUser->id,
            'diagnosis' => $diagnosis,
            'notes' => 'داده ساختگی برای ارائه پروژه است؛ نام اقلام، تعداد و قیمت‌ها صرفاً نمایشی‌اند و دستور درمان محسوب نمی‌شوند.',
        ]);
        if (!$prescription->save()) { throw new RuntimeException(json_encode($prescription->getErrors(), JSON_UNESCAPED_UNICODE)); }
        $added['prescriptions']++;
        $rows = [
            ['medicine', 'استامینوفن (نمونه)', 2, 15000],
            ['medicine', 'سرم (نمونه)', 1, 45000],
            ['supply', 'گاز استریل (نمونه)', 3, 10000],
            ['supply', 'ست سرم (نمونه)', 1, 25000],
        ];
        foreach ($rows as [$kind, $title, $quantity, $price]) {
            $reference=\app\models\MedicalItem::findOne(['kind'=>$kind,'title'=>$title]);
            if($reference===null){
                $reference=new \app\models\MedicalItem(['kind'=>$kind,'title'=>$title,'unit_price'=>$price,'active'=>1]);
                if(!$reference->save()){throw new RuntimeException('Could not create demonstration catalog item.');}
            }
            $item = new PrescriptionItem([
                'medical_item_id'=>$reference->id,
                'prescription_id' => $prescription->id, 'kind' => $kind, 'title' => $title,
                'quantity' => $quantity, 'unit_price' => $reference->unit_price,
                'dosage' => $kind === 'medicine' ? 'نمونه آموزشی' : null,
                'instructions' => 'صرفاً برای نمایش فرم، چاپ و محاسبه هزینه در پروژه.',
            ]);
            if (!$item->save()) { throw new RuntimeException(json_encode($item->getErrors(), JSON_UNESCAPED_UNICODE)); }
            $added['prescription_items']++;
        }
        if (\app\components\Billing::total($admission['id']) === null) { throw new RuntimeException('Demo total exceeds the supported range.'); }
    }
    if ($credentialLines !== []) {
        $text = "\nFictional demo accounts - http://localhost:8080/site/login\n" . implode("\n", $credentialLines);
        if (file_put_contents($credentialsPath, $text, FILE_APPEND | LOCK_EX) === false) { throw new RuntimeException('Could not save local demo credentials.'); }
        @chmod($credentialsPath, 0600);
    }
    $transaction->commit();
    echo 'Added: ', json_encode($added), PHP_EOL;
    echo "New account credentials: app/runtime/demo-staff-access.txt\n";
} catch (\Throwable $error) {
    if ($transaction->isActive) { $transaction->rollBack(); }
    throw $error;
}
