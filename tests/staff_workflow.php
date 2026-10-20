<?php
// Run against tests/staff-router.php and an EMPTY, isolated *_test database.
declare(strict_types=1);
$dsn = getenv('HOSPITAL_TEST_DSN') ?: '';
if (!preg_match('/dbname=[a-zA-Z0-9_]+_test(?:;|$)/', $dsn)) { throw new RuntimeException('Use an isolated _test database.'); }
$db = new PDO($dsn, getenv('HOSPITAL_TEST_USER') ?: 'root', getenv('HOSPITAL_TEST_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
if ((int) $db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn() !== 0) {
    throw new RuntimeException('Test database must be empty. No data was changed.');
}
$db->exec(file_get_contents(__DIR__ . '/../database/install.sql'));
$db->exec("INSERT INTO wards (name, floor) VALUES ('بخش آزمایشی', 'همکف')");
$seedAdmin = $db->prepare("INSERT INTO users (username,full_name,password_hash,auth_key,role,is_active) VALUES (?,?,?,?, 'admin',1)");
$seedAdmin->execute(['test.admin', 'مدیر آزمایشی', password_hash('StaffTest123!', PASSWORD_DEFAULT), bin2hex(random_bytes(32))]);
$base = getenv('HOSPITAL_TEST_URL') ?: 'http://127.0.0.1:8091';
$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    $checks++;
    echo 'PASS ', $message, PHP_EOL;
}
class Browser {
    private $curl;
    public string $token = '';
    public string $cookie = '';
    public function __construct(private string $base) {
        $this->curl = curl_init();
        curl_setopt_array($this->curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 15, CURLOPT_HEADER => true]);
    }
    public function request(string $path, ?array $data = null): array {
        $path = preg_replace_callback('/[^\x21-\x7e]/u', static fn($match) => rawurlencode($match[0]), $path);
        curl_setopt($this->curl, CURLOPT_URL, $this->base . $path);
        curl_setopt($this->curl, CURLOPT_POST, $data !== null);
        if ($data !== null) { curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data + ['_csrf' => $this->token])); }
        $response = curl_exec($this->curl);
        if ($response === false) { throw new RuntimeException(curl_error($this->curl)); }
        $size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $size);
        $html = substr($response, $size);
        if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $matches)) {
            $this->token = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        }
        if (preg_match('/Set-Cookie: (_hospital_test_identity=[^;]+)/i', $headers, $matches)) { $this->cookie = $matches[1]; }
        return [curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE), $html, $headers];
    }
    public function submit(string $path, array $data): array { $this->request($path); return $this->request($path, $data); }
}
function createStaff(Browser $admin, Browser $browser, string $username, string $name, string $role): void {
    [$status, $html] = $admin->submit('/user/create', ['UserForm' => [
        'username' => $username, 'full_name' => $name, 'role' => $role, 'specialty' => 'داخلی',
        'password' => 'StaffTest123!', 'password_repeat' => 'StaffTest123!',
        'doctor_id' => '', 'auth_key' => 'tampered', 'id' => 1, 'is_active' => 1,
    ]]);
    check($status === 302, "admin creates $role ($username)");
    check($browser->submit('/site/login', ['LoginForm'=>['username'=>$username,'password'=>'StaffTest123!']])[0] === 302, "new $role logs in");
    check($browser->request('/site/index')[0] === 200, "dashboard $username");
}
function nationalCode(string $prefix): string {
    $sum = 0;
    for ($i = 0; $i < 9; $i++) { $sum += (int) $prefix[$i] * (10 - $i); }
    $mod = $sum % 11;
    return $prefix . ($mod < 2 ? $mod : 11 - $mod);
}
$guest = new Browser($base);
check($guest->request('/admission/index')[0] === 302, 'guest redirected to login');
check($guest->request('/site/login')[0] === 200, 'login page renders');
$secretary = new Browser($base); $doctor = new Browser($base); $otherDoctor = new Browser($base); $discharge = new Browser($base);
$admin = new Browser($base);
check($admin->submit('/site/login', ['LoginForm'=>['username'=>'test.admin','password'=>'StaffTest123!']])[0] === 302, 'administrator logs in');
createStaff($admin, $secretary, 'test.secretary', 'منشی آزمایشی', 'secretary');
createStaff($admin, $doctor, 'test.doctor', 'پزشک آزمایشی اول', 'doctor');
createStaff($admin, $otherDoctor, 'test.other', 'پزشک آزمایشی دوم', 'doctor');
createStaff($admin, $discharge, 'test.discharge', 'ترخیص‌کار آزمایشی', 'discharge');
$user = $db->query("SELECT * FROM users WHERE username='test.doctor'")->fetch(PDO::FETCH_ASSOC);
$other = $db->query("SELECT * FROM users WHERE username='test.other'")->fetch(PDO::FETCH_ASSOC);
check((int) $user['doctor_id'] !== 1 && $user['auth_key'] !== 'tampered', 'account creation ignores posted record ID and auth key');
check(password_verify('StaffTest123!', $user['password_hash']) && $user['password_hash'] !== 'StaffTest123!', 'password stored as hash');
check($guest->request('/site/register')[0] === 404, 'public registration GET removed');
check($guest->request('/site/register', ['RegisterForm'=>['username'=>'intruder','role'=>'admin','password'=>'StaffTest123!']])[0] === 404, 'public registration POST removed');
check((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 5, 'blocked registration creates no account');
[$status] = $admin->submit('/user/create', ['UserForm'=>['username'=>'invalid.role','full_name'=>'نقش نامعتبر','role'=>'root','password'=>'StaffTest123!','password_repeat'=>'StaffTest123!']]);
check($status === 200 && (int) $db->query("SELECT COUNT(*) FROM users WHERE username='invalid.role'")->fetchColumn() === 0, 'unknown role rejected');
[$status] = $admin->submit('/user/create', ['UserForm'=>['username'=>'test.secretary','full_name'=>'تکراری','role'=>'secretary','password'=>'StaffTest123!','password_repeat'=>'StaffTest123!']]);
check($status === 200 && (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 5, 'duplicate username rejected');

$admissionIds = [];
foreach ([[$user['doctor_id'], 'اول', '008150001'], [$other['doctor_id'], 'دوم', '008150002']] as [$doctorId, $name, $prefix]) {
    $code = nationalCode($prefix);
    [$status] = $secretary->submit('/patient/create', ['Patient' => ['first_name' => 'بیمار', 'last_name' => $name, 'national_code' => $code, 'mobile' => '09121234567', 'birthDateInput' => '']]);
    check($status === 302, "secretary creates patient $name");
    $patientId = $db->query("SELECT id FROM patients WHERE national_code='$code'")->fetchColumn();
    [$status] = $secretary->submit('/admission/create?patient_id=' . $patientId, ['Admission' => ['patient_id' => 99999, 'doctor_id' => $doctorId, 'ward_id' => 1]]);
    check($status === 302, "secretary creates admission $name; route patient ID wins");
    $admissionIds[] = (int) $db->query('SELECT MAX(id) FROM admissions')->fetchColumn();
}
[$ownId, $otherId] = $admissionIds;
check($secretary->request('/index.php?r=patient/index')[0] === 200, 'script-style route also stays in isolated test application');
foreach ([[$doctor, ['/patient/index','/patient/create','/doctor/index','/ward/create','/admission/create','/admission/calendar','/admission/search-patients?q=بیمار','/admission/search-doctors?q=پزشک','/discharge/create']],
    [$secretary, ['/admission-service/create','/clinical/create?admission_id='.$ownId,'/clinical/print?admission_id='.$ownId,'/discharge/create','/admission/search-open-admissions?q=بیمار']],
    [$discharge, ['/patient/index','/patient/create','/doctor/account?id=1','/ward/index','/admission/create','/clinical/create?admission_id='.$ownId,'/clinical/print?admission_id='.$ownId,'/admission-service/create']]] as [$browser, $paths]) {
    foreach ($paths as $path) { check($browser->request($path)[0] === 403, 'role denied ' . $path); }
}
foreach ([$secretary, $doctor, $discharge] as $browser) {
    foreach (['/user/index', '/user/create', '/user/update?id=1'] as $path) { check($browser->request($path)[0] === 403, 'non-admin cannot access ' . $path); }
    check($browser->request('/user/create', ['UserForm'=>['role'=>'admin','username'=>'intruder']])[0] === 403, 'non-admin POST cannot create users');
}
check($secretary->request('/doctor/account?id=1')[0] === 403, 'secretary can no longer create physician accounts');
check($doctor->request('/admission/view?id='.$ownId)[0] === 200, 'doctor opens own admission');
foreach (['/admission/view?id=', '/clinical/create?admission_id=', '/clinical/print?admission_id=', '/admission-service/create?admission_id='] as $route) {
    check($doctor->request($route.$otherId)[0] === 404, 'unassigned admission hidden at '.$route);
}
[$status, $html] = $doctor->request('/admission/index');
check($status === 200 && !str_contains($html, nationalCode('008150002')), 'doctor list excludes other patient');
[$status, $json] = $doctor->request('/admission/search-open-admissions?q='.nationalCode('008150002'));
check($status === 200 && json_decode($json, true) === [], 'AJAX lookup excludes unassigned patient');
[$status, $html] = $doctor->submit('/admission-service/create', ['AdmissionService'=>['service_id'=>1,'quantity'=>1]]);
check($status === 200 && str_contains($html, 'پذیرش بیمار'), 'empty admission selection returns form validation');
check($doctor->request('/discharge/create?admission_id='.$ownId, ['Discharge'=>['admission_id'=>$ownId]])[0] === 403, 'doctor cannot bypass role restrictions by POST');
check($discharge->request('/patient/create', ['Patient'=>['first_name'=>'unauthorized']])[0] === 403, 'discharge user cannot POST patient creation');

[$status] = $doctor->submit('/admission-service/create', ['AdmissionService' => ['admission_id' => $otherId, 'service_id' => 1, 'quantity' => 1]]);
check($status === 404 && (int) $db->query('SELECT COUNT(*) FROM admission_services')->fetchColumn() === 0, 'tampered POST cannot add service to another doctor');
[$status] = $doctor->submit('/admission-service/create?admission_id='.$ownId, ['AdmissionService' => ['admission_id' => $otherId, 'service_id' => 1, 'quantity' => 1, 'unit_price' => 1]]);
$line = $db->query('SELECT * FROM admission_services')->fetch(PDO::FETCH_ASSOC);
check($status === 302 && (int) $line['admission_id'] === $ownId && (int) $line['unit_price'] === 100000, 'service uses locked route and catalog price');
$prescriptionData = ['Prescription' => ['diagnosis' => 'تشخیص آزمایشی', 'notes' => '<script>alert(1)</script>', 'admission_id' => $otherId, 'doctor_id' => $other['doctor_id'], 'user_id' => $other['id']],
    'PrescriptionItem' => [
        ['kind' => 'medicine', 'title' => 'داروی آزمایشی', 'quantity' => '۲', 'unit_price' => '۲۰۰۰۰', 'dosage' => 'هر ۱۲ ساعت یک عدد', 'instructions' => 'پس از غذا', 'prescription_id' => 999],
        ['kind' => 'supply', 'title' => 'پانسمان', 'quantity' => 3, 'unit_price' => 5000],
    ]];
check($doctor->request('/clinical/create?admission_id='.$otherId, $prescriptionData)[0] === 404, 'POST cannot create prescription for unassigned admission');
check($secretary->request('/clinical/create?admission_id='.$ownId, $prescriptionData)[0] === 403, 'secretary cannot POST prescriptions');
[$status] = $doctor->submit('/clinical/create?admission_id='.$ownId, $prescriptionData);
$record = $db->query('SELECT * FROM prescriptions')->fetch(PDO::FETCH_ASSOC);
check($status === 302 && (int) $record['admission_id'] === $ownId && (int) $record['doctor_id'] === (int) $user['doctor_id'] && (int) $record['user_id'] === (int) $user['id'], 'prescription ownership assigned on server');
check((int) $db->query('SELECT COUNT(*) FROM prescription_items')->fetchColumn() === 2, 'medicine and supplies saved together; Persian digits accepted');
[$status, $html] = $doctor->request('/clinical/print?admission_id='.$ownId);
check($status === 200 && str_contains($html, 'پانسمان') && str_contains($html, 'هر ۱۲ ساعت') && !str_contains($html, '<script>alert(1)</script>'), 'print includes instructions and escapes notes');
$invalid = $prescriptionData; $invalid['PrescriptionItem'][1]['quantity'] = 0;
[$status] = $doctor->submit('/clinical/create?admission_id='.$ownId, $invalid);
check($status === 200 && (int) $db->query('SELECT COUNT(*) FROM prescriptions')->fetchColumn() === 1, 'invalid item leaves no partial prescription');
$tooMany = $prescriptionData; $tooMany['PrescriptionItem'] = array_fill(0, 51, $prescriptionData['PrescriptionItem'][0]);
check($doctor->submit('/clinical/create?admission_id='.$ownId, $tooMany)[0] === 400, 'row limit enforced on server');
check($secretary->request('/admission/view?id='.$ownId)[0] === 200, 'secretary sees admission summary');
[$status, $html] = $secretary->request('/admission/view?id='.$ownId);
check(!str_contains($html, 'تشخیص آزمایشی') && !str_contains($html, 'ثبت نسخه، دارو'), 'secretary has no clinical controls or notes');
[$status, $html] = $discharge->request('/discharge/create?admission_id='.$ownId);
check($status === 200 && str_contains($html, 'پانسمان') && !str_contains($html, 'تشخیص آزمایشی'), 'discharge sees itemized costs without clinical notes');
[$status] = $discharge->submit('/discharge/create?admission_id='.$ownId, ['Discharge' => ['admission_id' => $otherId, 'total_amount' => 1, 'discharge_date' => '2000-01-01', 'description' => 'تست ترخیص']]);
$settlement = $db->query('SELECT * FROM discharges')->fetch(PDO::FETCH_ASSOC);
check($status === 302 && (int) $settlement['admission_id'] === $ownId && (int) $settlement['total_amount'] === 155000, 'discharge locks route and totals services plus medicines plus supplies');
check($db->query('SELECT status FROM admissions WHERE id='.$ownId)->fetchColumn() === 'discharged', 'admission status updated with discharge');
check($doctor->submit('/clinical/create?admission_id='.$ownId, $prescriptionData)[0] === 403, 'cannot prescribe after discharge');
check($doctor->submit('/admission-service/create?admission_id='.$ownId, ['AdmissionService'=>['service_id'=>1,'quantity'=>1]])[0] === 403, 'cannot add service after discharge');
check($doctor->request('/clinical/print?admission_id='.$ownId)[0] === 200, 'own historical prescription remains printable');
[$status] = $admin->submit('/user/create?doctor_id=1', ['UserForm' => ['username'=>'test.existing','password'=>'StaffTest123!','password_repeat'=>'StaffTest123!','role'=>'discharge','full_name'=>'forged','doctor_id'=>$other['doctor_id']]]);
$linked = $db->query("SELECT role,doctor_id,full_name FROM users WHERE username='test.existing'")->fetch(PDO::FETCH_ASSOC);
check($status === 302 && $linked['role'] === 'doctor' && (int) $linked['doctor_id'] === 1 && $linked['full_name'] !== 'forged', 'admin links existing doctor with locked route identity');
check($doctor->request('/site/logout')[0] === 405, 'logout requires POST');
$doctor->request('/site/index');
check($doctor->request('/site/logout', [])[0] === 302, 'logout works');
check($doctor->request('/admission/index')[0] === 302, 'session ends after logout');
[$status, $html] = $doctor->submit('/site/login', ['LoginForm'=>['username'=>'test.doctor','password'=>'wrong-password','rememberMe'=>1]]);
check($status === 200 && str_contains($html, 'نام کاربری یا رمز عبور درست نیست'), 'incorrect password rejected');
check($doctor->submit('/site/login', ['LoginForm'=>['username'=>'test.doctor','password'=>'StaffTest123!','rememberMe'=>1]])[0] === 302, 'existing account logs in');
$remember = curl_init($base.'/site/index');
curl_setopt_array($remember, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIE=>$doctor->cookie]);
curl_exec($remember);
check(curl_getinfo($remember, CURLINFO_RESPONSE_CODE) === 200, 'remember-me cookie restores identity without session cookie');
curl_close($remember);

foreach (['/user/index','/user/create','/patient/index','/patient/create','/doctor/index','/ward/index','/admission/calendar','/clinical/print?admission_id='.$ownId,'/discharge/create'] as $path) {
    check($admin->request($path)[0] === 200, 'administrator accesses ' . $path);
}
check($admin->request('/site/register')[0] === 404, 'registration is removed even for administrator');
[$status] = $admin->submit('/admission-service/create?admission_id='.$otherId, ['AdmissionService'=>['service_id'=>1,'quantity'=>1]]);
check($status === 302, 'admin can register services without a personal doctor profile');
[$status] = $admin->submit('/clinical/create?admission_id='.$otherId, ['Prescription'=>['diagnosis'=>'ثبت توسط مدیر'],
    'PrescriptionItem'=>[['kind'=>'supply','title'=>'سرم آزمایشی','quantity'=>1,'unit_price'=>1000]]]);
$adminRecord = $db->query('SELECT * FROM prescriptions WHERE admission_id='.$otherId)->fetch(PDO::FETCH_ASSOC);
check($status === 302 && (int) $adminRecord['doctor_id'] === (int) $other['doctor_id'] && (int) $adminRecord['user_id'] === 1, 'admin prescription retains assigned physician and actual author');
[$status,$html] = $admin->request('/clinical/print?admission_id='.$otherId);
check($status === 200 && str_contains($html, 'ثبت‌کننده:') && str_contains($html, 'مدیر آزمایشی'), 'print identifies administrative author');

$adminFields = ['username'=>'test.admin','full_name'=>'مدیر آزمایشی','role'=>'admin','is_active'=>1,'password'=>'','password_repeat'=>''];
$demote = $adminFields; $demote['role'] = 'secretary';
check($admin->submit('/user/update?id=1',['UserForm'=>$demote])[0] === 200 && $db->query('SELECT role FROM users WHERE id=1')->fetchColumn() === 'admin', 'cannot demote last active administrator');
$deactivate = $adminFields; $deactivate['is_active'] = 0;
check($admin->submit('/user/update?id=1',['UserForm'=>$deactivate])[0] === 200 && (int) $db->query('SELECT is_active FROM users WHERE id=1')->fetchColumn() === 1, 'cannot deactivate last administrator');
check($admin->request('/user/delete?id=2')[0] === 404, 'account deletion route does not exist');

$doctorFields = ['username'=>$user['username'],'full_name'=>$user['full_name'],'role'=>'doctor','doctor_id'=>$user['doctor_id'],'is_active'=>0,'password'=>'','password_repeat'=>''];
$oldCookie = $doctor->cookie;
check($admin->submit('/user/update?id='.$user['id'],['UserForm'=>$doctorFields])[0] === 302, 'admin deactivates a staff account');
check($doctor->request('/site/index')[0] === 302, 'deactivation ends existing authenticated session');
$remember = curl_init($base.'/site/index');
curl_setopt_array($remember,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>$oldCookie]); curl_exec($remember);
check(curl_getinfo($remember,CURLINFO_RESPONSE_CODE) === 302, 'deactivated account cannot return with remembered cookie'); curl_close($remember);
check($doctor->submit('/site/login',['LoginForm'=>['username'=>'test.doctor','password'=>'StaffTest123!']])[0] === 200, 'inactive account cannot log in');
$doctorFields['is_active']=1; $doctorFields['password']=$doctorFields['password_repeat']='UpdatedStaff123!';
check($admin->submit('/user/update?id='.$user['id'],['UserForm'=>$doctorFields])[0] === 302, 'admin reactivates account and resets password');
check($doctor->submit('/site/login',['LoginForm'=>['username'=>'test.doctor','password'=>'StaffTest123!']])[0] === 200, 'old password rejected after reset');
check($doctor->submit('/site/login',['LoginForm'=>['username'=>'test.doctor','password'=>'UpdatedStaff123!']])[0] === 302, 'new password accepted');
$doctorFields['password']=$doctorFields['password_repeat']=''; $doctorFields['role']='discharge';
check($admin->submit('/user/update?id='.$user['id'],['UserForm'=>$doctorFields])[0] === 302, 'admin changes a staff role');
$changed = $db->query('SELECT role,doctor_id FROM users WHERE id='.$user['id'])->fetch(PDO::FETCH_ASSOC);
check($changed['role'] === 'discharge' && $changed['doctor_id'] === null, 'non-physician role cannot retain doctor binding');
check($doctor->request('/clinical/print?admission_id='.$ownId)[0] !== 200, 'old session loses clinical permission after role change');
check((int) $db->query('SELECT COUNT(*) FROM prescriptions WHERE admission_id='.$ownId)->fetchColumn() === 1, 'account updates preserve prescription history');

$secondAdmin = new Browser($base);
createStaff($admin,$secondAdmin,'test.admin2','مدیر دوم','admin');
check($secondAdmin->submit('/user/update?id=1',['UserForm'=>$demote])[0] === 302, 'another active administrator permits role change');
check($admin->request('/user/index')[0] !== 200, 'demoted administrator loses user-management permission');
$secondId=(int) $db->query("SELECT id FROM users WHERE username='test.admin2'")->fetchColumn();
$secondFields=['username'=>'test.admin2','full_name'=>'مدیر دوم','role'=>'admin','is_active'=>0];
check($secondAdmin->submit('/user/update?id='.$secondId,['UserForm'=>$secondFields])[0] === 200, 'remaining administrator is still protected');
$beforeUpgrade=(int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$db->exec(file_get_contents(__DIR__.'/../database/upgrade_admin_role.sql'));
check((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === $beforeUpgrade, 'admin schema upgrade is repeatable and preserves accounts');
echo "SUCCESS: $checks checks passed.\n";
