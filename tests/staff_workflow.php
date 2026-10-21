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
$user = $db->query("SELECT u.*,d.id AS doctor_id FROM users u LEFT JOIN doctors d ON d.user_id=u.id WHERE u.username='test.doctor'")->fetch(PDO::FETCH_ASSOC);
$other = $db->query("SELECT u.*,d.id AS doctor_id FROM users u LEFT JOIN doctors d ON d.user_id=u.id WHERE u.username='test.other'")->fetch(PDO::FETCH_ASSOC);
check((int) $user['doctor_id'] !== 1 && $user['auth_key'] !== 'tampered', 'account creation ignores posted record ID and auth key');
check((int) $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='users' AND column_name='doctor_id'")->fetchColumn() === 0,
    'fresh schema contains no users.doctor_id column');
check((int) $db->query('SELECT user_id FROM doctors WHERE id='.$user['doctor_id'])->fetchColumn() === (int) $user['id'],
    'physician profile owns the account foreign key');
[$status] = $admin->submit('/user/create', ['UserForm' => [
    'username'=>'duplicate.doctor', 'full_name'=>'تکراری', 'role'=>'doctor', 'doctor_id'=>$user['doctor_id'],
    'password'=>'StaffTest123!', 'password_repeat'=>'StaffTest123!',
]]);
check($status === 200 && (int) $db->query("SELECT COUNT(*) FROM users WHERE username='duplicate.doctor'")->fetchColumn() === 0,
    'an already linked physician cannot receive a second account');
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
[$status, $html] = $doctor->request('/site/index');
check($status === 200 && str_contains($html, 'data-admission-id="'.$ownId.'"')
    && !str_contains($html, 'data-admission-id="'.$otherId.'"'), 'doctor dashboard shows only assigned active admissions');
[$status, $html] = $otherDoctor->request('/site/index');
check($status === 200 && str_contains($html, 'data-admission-id="'.$otherId.'"')
    && !str_contains($html, 'data-admission-id="'.$ownId.'"'), 'second doctor dashboard has its own scoped worklist');
foreach ([$secretary, $admin] as $browser) {
    [$status, $html] = $browser->request('/site/index');
    check($status === 200 && str_contains($html, 'پذیرش‌های اخیر')
        && str_contains($html, 'data-admission-id="'.$ownId.'"')
        && str_contains($html, 'data-admission-id="'.$otherId.'"'), 'secretary and admin dashboards show recent admissions');
}
[$status, $html] = $discharge->request('/site/index');
check($status === 200 && str_contains($html, 'پرونده‌ای در صف ترخیص نیست.')
    && !str_contains($html, 'data-admission-id='), 'discharge dashboard explains the empty queue before approval');
foreach ([[$doctor, $ownId, $otherId], [$otherDoctor, $otherId, $ownId]] as [$browser, $allowedId, $deniedId]) {
    [$status, $json, $headers] = $browser->request('/site/dashboard');
    $payload = json_decode($json, true);
    check($status === 200 && isset($payload['updatedAt'], $payload['html'])
        && str_contains($payload['html'], 'data-admission-id="'.$allowedId.'"')
        && !str_contains($payload['html'], 'data-admission-id="'.$deniedId.'"'), 'live dashboard preserves doctor ownership');
    check(str_contains(strtolower($headers), 'no-store')
        && !str_contains($payload['html'], '<html'), 'live dashboard returns only an uncached fragment');
}
foreach ([$secretary, $admin, $discharge] as $browser) {
    [$status, $json] = $browser->request('/site/dashboard');
    check($status === 200 && isset(json_decode($json, true)['html']), 'live dashboard available to authorized role');
}
foreach ([$doctor, $secretary] as $browser) {
    check($browser->request('/discharge/queue')[0] === 403, 'live discharge queue denied outside discharge and admin roles');
}
foreach ([$admin, $discharge] as $browser) {
    [$status, $json, $headers] = $browser->request('/discharge/queue');
    $payload = json_decode($json, true);
    check($status === 200 && isset($payload['updatedAt'])
        && !str_contains($payload['html'], 'data-admission-id=')
        && str_contains(strtolower($headers), 'no-store'), 'live empty discharge queue is uncached and valid JSON');
}
check($doctor->submit('/site/dashboard', [])[0] === 405, 'live dashboard is read only');
check($discharge->submit('/discharge/queue', [])[0] === 405, 'live discharge queue is read only');
check(in_array($guest->request('/site/dashboard')[0], [302, 403], true), 'guest cannot refresh dashboard data');
$ownPatient=(int)$db->query('SELECT patient_id FROM admissions WHERE id='.$ownId)->fetchColumn();
$beforeAdmissions=(int)$db->query('SELECT COUNT(*) FROM admissions')->fetchColumn();
[$status,$html]=$secretary->submit('/admission/create?patient_id='.$ownPatient,['Admission'=>['doctor_id'=>$user['doctor_id'],'ward_id'=>1]]);
check($status===200 && str_contains($html,'پذیرش فعال دارد') && (int)$db->query('SELECT COUNT(*) FROM admissions')->fetchColumn()===$beforeAdmissions,'duplicate active admission rejected without changing existing record');
check(str_contains($html,'مشاهدهٔ پروندهٔ فعال') && str_contains($html,'/admission/view?id='.$ownId),'duplicate admission form links to existing active case');
$db->exec("UPDATE patients SET birth_date='2020-01-01' WHERE id=".$ownPatient);
[$status,$html]=$secretary->request('/patient/update?id='.$ownPatient);
check($status===200 && str_contains($html,'1398/10/11'),'patient edit preloads Jalali birth date');
$patientEdit=['Patient'=>['id'=>999,'created_at'=>'2000-01-01','first_name'=>'بیمار','last_name'=>'اول','national_code'=>nationalCode('008150001'),'mobile'=>'09121234568','birthDateInput'=>'1398/10/11']];
check($secretary->submit('/patient/update?id='.$ownPatient,$patientEdit)[0]===302 && $db->query('SELECT mobile FROM patients WHERE id='.$ownPatient)->fetchColumn()==='09121234568','secretary edits patient using locked route identity');
check($db->query('SELECT birth_date FROM patients WHERE id='.$ownPatient)->fetchColumn()==='2020-01-01','unchanged Jalali date round trips on patient edit');
check($doctor->request('/patient/update?id='.$ownPatient)[0]===403,'doctor cannot edit patient master data');
check($secretary->request('/patient/update?id=999999')[0]===404,'unknown patient edit returns not found');
$oldDoctorName=$db->query('SELECT doctor_name FROM admissions WHERE id='.$ownId)->fetchColumn();
$doctorEdit=['Doctor'=>['id'=>999,'user_id'=>$other['id'],'name'=>'پزشک ویرایش‌شده','specialty'=>'داخلی','mobile'=>'09121234567']];
check($admin->submit('/doctor/update?id='.$user['doctor_id'],$doctorEdit)[0]===302,'admin edits physician master data');
check((int) $db->query('SELECT user_id FROM doctors WHERE id='.$user['doctor_id'])->fetchColumn() === (int) $user['id'],
    'physician form cannot overwrite its internal account binding');
check($db->query('SELECT full_name FROM users WHERE id='.$user['id'])->fetchColumn()==='پزشک ویرایش‌شده','physician edit synchronizes linked account name');
check($db->query('SELECT doctor_name FROM admissions WHERE id='.$ownId)->fetchColumn()===$oldDoctorName,'physician edit preserves admission historical name');
check($admin->submit('/ward/update?id=1',['Ward'=>['name'=>'بخش ویرایش‌شده','floor'=>'دوم','id'=>999]])[0]===302,'admin edits ward master data');
check($db->query('SELECT ward FROM admissions WHERE id='.$ownId)->fetchColumn()==='بخش آزمایشی','ward edit preserves historical admission name');
check($secretary->request('/ward/update?id=1')[0]===403 && $doctor->request('/doctor/update?id='.$user['doctor_id'])[0]===403,'non-admin cannot edit physician or ward');
$inactivePatientCode=nationalCode('008150003');
check($secretary->submit('/patient/create',['Patient'=>['first_name'=>'بیمار','last_name'=>'وضعیت','national_code'=>$inactivePatientCode,'mobile'=>'09121234567']])[0]===302,'patient fixture for inactive reference checks');
$inactivePatientId=(int)$db->query("SELECT id FROM patients WHERE national_code='$inactivePatientCode'")->fetchColumn();
$doctorEdit['Doctor']['active']=0;
check($admin->submit('/doctor/update?id='.$user['doctor_id'],$doctorEdit)[0]===302,'admin disables doctor for new admissions');
check((int)$db->query('SELECT is_active FROM users WHERE id='.$user['id'])->fetchColumn()===1 && $doctor->request('/admission/view?id='.$ownId)[0]===200,'inactive doctor keeps account and assigned historical admission');
[$status,$json]=$secretary->request('/admission/search-doctors?q=پزشک');
check($status===200 && !in_array((int)$user['doctor_id'],array_column(json_decode($json,true),'id'),true),'inactive doctor excluded from admission search');
check($secretary->submit('/admission/create?patient_id='.$inactivePatientId,['Admission'=>['doctor_id'=>$user['doctor_id'],'ward_id'=>1]])[0]===200,'posted inactive doctor rejected');
$doctorEdit['Doctor']['active']=1;
check($admin->submit('/doctor/update?id='.$user['doctor_id'],$doctorEdit)[0]===302,'admin reactivates doctor');
$wardEdit=['Ward'=>['name'=>'بخش ویرایش‌شده','floor'=>'دوم','active'=>0]];
check($admin->submit('/ward/update?id=1',$wardEdit)[0]===302,'admin disables ward for new admissions');
[$status,$html]=$secretary->request('/admission/create?patient_id='.$inactivePatientId);
check($status===200 && !str_contains($html,'<option value="1">بخش ویرایش‌شده'),'inactive ward excluded from admission form');
check($secretary->submit('/admission/create?patient_id='.$inactivePatientId,['Admission'=>['doctor_id'=>$user['doctor_id'],'ward_id'=>1]])[0]===200,'posted inactive ward rejected');
check((int)$db->query('SELECT COUNT(*) FROM admissions WHERE patient_id='.$inactivePatientId)->fetchColumn()===0,'inactive references create no admission');
$wardEdit['Ward']['active']=1;
check($admin->submit('/ward/update?id=1',$wardEdit)[0]===302,'admin reactivates ward');
check($admin->submit('/service/create',['Service'=>['title'=>'خدمت آزمایشی جدید','price'=>'۱۲۰۰۰','active'=>1]])[0]===302,'admin creates service with Persian price');
$newServiceId=(int)$db->query("SELECT id FROM services WHERE title='خدمت آزمایشی جدید'")->fetchColumn();
check((int)$db->query('SELECT price FROM services WHERE id='.$newServiceId)->fetchColumn()===12000,'service tariff normalizes digits');
check($admin->submit('/service/update?id='.$newServiceId,['Service'=>['title'=>'خدمت آزمایشی جدید','price'=>18000,'active'=>0]])[0]===302,'admin updates service tariff and deactivates reference');
check($admin->submit('/service/create',['Service'=>['title'=>'خدمت آزمایشی جدید','price'=>100,'active'=>1]])[0]===200,'duplicate service title rejected');
foreach ([$doctor,$secretary,$discharge] as $nonAdmin) {
    check($nonAdmin->request('/service/index')[0]===403 && $nonAdmin->request('/service/update?id=1',['Service'=>['price'=>1]])[0]===403,'service management restricted to admin');
}
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
foreach (['medicine'=>['داروی آزمایشی',20000],'supply'=>['پانسمان',5000]] as $kind=>[$title,$price]) {
    check($admin->submit('/medical-item/create',['MedicalItem'=>['kind'=>$kind,'title'=>$title,'unit_price'=>$price,'active'=>1]])[0]===302,'administrator defines '.$kind.' catalog item');
}
$medicineId=(int)$db->query("SELECT id FROM medical_items WHERE title='داروی آزمایشی'")->fetchColumn();
$supplyId=(int)$db->query("SELECT id FROM medical_items WHERE title='پانسمان'")->fetchColumn();
check($doctor->request('/medical-item/create')[0]===403,'doctor cannot manage catalog');
check($secretary->request('/medical-item/search?q=دارو&kind=medicine')[0]===403,'secretary cannot query treatment catalog');
[$status,$json]=$doctor->request('/medical-item/search?q=داروی&kind=medicine');
check($status===200 && (int)json_decode($json,true)[0]['id']===$medicineId,'catalog search returns permitted active kind');
$prescriptionData = ['Prescription' => ['diagnosis' => 'تشخیص آزمایشی', 'notes' => '<script>alert(1)</script>', 'admission_id' => $otherId, 'doctor_id' => $other['doctor_id'], 'user_id' => $other['id']],
    'PrescriptionItem' => [
        ['medical_item_id'=>$medicineId, 'kind' => 'medicine', 'title' => 'نام دستکاری‌شده', 'quantity' => '۲', 'unit_price' => '۲۰۰۰۰', 'dosage' => 'هر ۱۲ ساعت یک عدد', 'instructions' => 'پس از غذا', 'prescription_id' => 999],
        ['medical_item_id'=>$supplyId, 'kind' => 'supply', 'title' => 'پانسمان', 'quantity' => 3, 'unit_price' => 5000],
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
// Corrections preserve ownership and snapshots, and atomically append their history.
function correctionPayload(PDO $db,int $id,int $medicineQuantity): array {
    $p=$db->query('SELECT * FROM prescriptions WHERE id='.$id)->fetch(PDO::FETCH_ASSOC);
    $rows=$db->query('SELECT * FROM prescription_items WHERE prescription_id='.$id.' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as &$row){$row['item_id']=$row['id'];if($row['kind']==='medicine')$row['quantity']=$medicineQuantity;$row['unit_price']=1;$row['title']='forged';}unset($row);
    return ['Prescription'=>['diagnosis'=>$p['diagnosis'],'notes'=>$p['notes'],'admission_id'=>99999],
        'PrescriptionItem'=>$rows,'CorrectionForm'=>['reason'=>'اصلاح تعداد نسخه','revision'=>$p['revision']]];
}
$prescriptionId=(int)$record['id'];$serviceLineId=(int)$line['id'];
function readyPayload(PDO $db,int $admissionId,string $ready='1'): array {
    return ['ready'=>$ready,'treatment_version'=>$db->query('SELECT treatment_version FROM admissions WHERE id='.$admissionId)->fetchColumn()];
}
check($doctor->request('/admission/ready?id='.$ownId)[0]===405,'approval requires POST');
check($secretary->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId))[0]===403,'secretary cannot approve discharge');
check($otherDoctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId))[0]===404,'unassigned doctor cannot approve discharge');
check($discharge->submit('/discharge/create?admission_id='.$ownId,['Discharge'=>['admission_id'=>$ownId]])[0]===200 && (int)$db->query('SELECT COUNT(*) FROM discharges')->fetchColumn()===0,'discharge rejected without medical approval');
check($doctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId))[0]===302,'assigned physician approves discharge');
[$status, $json] = $discharge->request('/discharge/queue');
$payload = json_decode($json, true);
check($status === 200 && str_contains($payload['html'], 'data-admission-id="'.$ownId.'"')
    && !str_contains($payload['html'], 'data-admission-id="'.$otherId.'"')
    && str_contains($payload['html'], 'مدت انتظار'), 'live queue adds newly approved case with elapsed waiting time');
[$status, $html] = $discharge->request('/site/index');
check($status === 200 && str_contains($html, 'data-admission-id="'.$ownId.'"')
    && !str_contains($html, 'data-admission-id="'.$otherId.'"')
    && str_contains($html, 'مدت انتظار')
    && str_contains($html, '/discharge/create?admission_id='.$ownId), 'discharge dashboard shows only approved cases with waiting time and direct action');
$staleApproval=readyPayload($db,$ownId);
[$status,$json]=$discharge->request('/admission/search-open-admissions?q='.nationalCode('008150002'));
check($status===200 && json_decode($json,true)===[],'discharge lookup excludes unapproved admissions');
[$status,$html]=$discharge->request('/discharge/create');
check($status===200 && str_contains($html,'صف آمادهٔ ترخیص') && str_contains($html,nationalCode('008150001')) && !str_contains($html,nationalCode('008150002')),'discharge queue shows approved records only');
$db->exec('UPDATE services SET price=120000 WHERE id=1');
[$status,$html]=$doctor->request('/admission-service/update?id='.$serviceLineId);
check($status===200 && str_contains($html,'data-price="100000"') && str_contains($html,'قیمت ثبت‌شده'),'service preview preserves original tariff after price change');
$db->exec('UPDATE services SET price=100000 WHERE id=1');
$serviceEdit=['AdmissionService'=>['admission_id'=>$otherId,'service_id'=>1,'quantity'=>2,'unit_price'=>1],'CorrectionForm'=>['reason'=>'اصلاح تعداد خدمت','revision'=>1]];
check($doctor->submit('/admission-service/update?id='.$serviceLineId,$serviceEdit)[0]===302,'service correction accepted with locked admission');
check($db->query('SELECT ready_at FROM admissions WHERE id='.$ownId)->fetchColumn()===null,'service correction invalidates medical approval');
[$status, $json] = $discharge->request('/discharge/queue');
check($status === 200 && !str_contains(json_decode($json, true)['html'], 'data-admission-id="'.$ownId.'"'), 'live queue drops a case whose approval was revoked');
[$status, $html] = $discharge->request('/site/index');
check($status === 200 && !str_contains($html, 'data-admission-id="'.$ownId.'"'), 'treatment correction removes the case from the discharge dashboard');
check($doctor->submit('/admission/ready?id='.$ownId,$staleApproval)[0]===409,'stale approval cannot certify changed treatment');
check((int)$db->query('SELECT unit_price FROM admission_services WHERE id='.$serviceLineId)->fetchColumn()===100000,'service correction preserves historical tariff');
check($doctor->submit('/admission-service/update?id='.$serviceLineId,$serviceEdit)[0]===409,'stale service correction blocked');
$serviceEdit['AdmissionService']['quantity']=1;$serviceEdit['CorrectionForm']['revision']=2;
check($doctor->submit('/admission-service/update?id='.$serviceLineId,$serviceEdit)[0]===302,'second service correction restores count');
check($otherDoctor->request('/admission-service/update?id='.$serviceLineId)[0]===404,'other doctor cannot edit service');
$edit=correctionPayload($db,$prescriptionId,3);
check($doctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId))[0]===302,'physician reapproves latest treatment');
check($doctor->submit('/clinical/update?id='.$prescriptionId,$edit)[0]===302,'doctor corrects own prescription');
check($db->query('SELECT ready_at FROM admissions WHERE id='.$ownId)->fetchColumn()===null,'prescription correction invalidates medical approval');
check($doctor->submit('/clinical/update?id='.$prescriptionId,$edit)[0]===409,'stale prescription correction blocked');
check($otherDoctor->request('/clinical/update?id='.$prescriptionId)[0]===404,'other doctor cannot edit prescription');
check($secretary->request('/clinical/update?id='.$prescriptionId)[0]===403,'secretary cannot edit prescription');
$tariff=['MedicalItem'=>['kind'=>'medicine','title'=>'داروی آزمایشی','unit_price'=>30000,'active'=>0]];
check($admin->submit('/medical-item/update?id='.$medicineId,$tariff)[0]===302,'administrator changes tariff and disables item');
[$status,$html]=$doctor->request('/clinical/update?id='.$prescriptionId);
check($status===200 && str_contains($html,'data-original-price="20000"'),'prescription preview carries original tariff independently of catalog');
$edit=correctionPayload($db,$prescriptionId,2);
check($doctor->submit('/clinical/update?id='.$prescriptionId,$edit)[0]===302,'unchanged inactive catalog snapshot stays editable');
check((int)$db->query("SELECT unit_price FROM prescription_items WHERE kind='medicine' AND prescription_id=".$prescriptionId)->fetchColumn()===20000,'historical prescription price survives catalog changes and spoofed price');
$inactive=$prescriptionData;
check($doctor->submit('/clinical/create?admission_id='.$ownId,$inactive)[0]===200 && (int)$db->query('SELECT COUNT(*) FROM prescriptions')->fetchColumn()===1,'new prescriptions reject inactive catalog items atomically');
$tariff['MedicalItem']['active']=1;$tariff['MedicalItem']['unit_price']=20000;
check($admin->submit('/medical-item/update?id='.$medicineId,$tariff)[0]===302,'catalog item reactivated');
$forged=correctionPayload($db,$prescriptionId,2);$forged['PrescriptionItem'][0]['item_id']=999999;
check($doctor->submit('/clinical/update?id='.$prescriptionId,$forged)[0]===400,'foreign item identity rejected');
[$status,$html]=$doctor->request('/record-change/index?type=prescription&id='.$prescriptionId);
check($status===200 && str_contains($html,'پیش از اصلاح') && str_contains($html,'پس از اصلاح') && str_contains($html,'اصلاح تعداد نسخه'),'correction history contains before/after and reason');
check($otherDoctor->request('/record-change/index?type=prescription&id='.$prescriptionId)[0]===404,'correction history also enforces admission ownership');
check($discharge->request('/record-change/index?type=prescription&id='.$prescriptionId)[0]===403,'financial role cannot see clinical history');
check((int)$db->query('SELECT COUNT(*) FROM record_changes')->fetchColumn()===4,'only successful corrections append history');
[$status,$html]=$doctor->request('/admission/view?id='.$ownId);
check($status===200 && str_contains($html,'ثبت نسخه') && str_contains($html,'اصلاح نسخه'),'clinical timeline includes prescription and correction events');
[$status,$html]=$discharge->request('/admission/view?id='.$ownId);
check($status===200 && !str_contains($html,'اصلاح تعداد نسخه') && !str_contains($html,'تشخیص آزمایشی'),'financial timeline excludes clinical content');
check($discharge->request('/discharge/receipt?admission_id='.$ownId)[0]===404,'receipt unavailable before discharge');
check($doctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId))[0]===302,'physician confirms final treatment');
check($doctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId,'0'))[0]===302 && $db->query('SELECT ready_at FROM admissions WHERE id='.$ownId)->fetchColumn()===null,'physician may revoke approval before discharge');
check($doctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId))[0]===302,'physician approves again after review');
[$status] = $discharge->submit('/discharge/create?admission_id='.$ownId, ['Discharge' => ['admission_id' => $otherId, 'total_amount' => 1, 'discharge_date' => '2000-01-01', 'description' => 'تست ترخیص']]);
$settlement = $db->query('SELECT * FROM discharges')->fetch(PDO::FETCH_ASSOC);
check($status === 302 && (int) $settlement['admission_id'] === $ownId && (int) $settlement['total_amount'] === 155000, 'discharge locks route and totals services plus medicines plus supplies');
check($db->query('SELECT status FROM admissions WHERE id='.$ownId)->fetchColumn() === 'discharged', 'admission status updated with discharge');
check($doctor->submit('/clinical/create?admission_id='.$ownId, $prescriptionData)[0] === 403, 'cannot prescribe after discharge');
check($doctor->submit('/admission-service/create?admission_id='.$ownId, ['AdmissionService'=>['service_id'=>1,'quantity'=>1]])[0] === 403, 'cannot add service after discharge');
check($doctor->request('/clinical/print?admission_id='.$ownId)[0] === 200, 'own historical prescription remains printable');
check($doctor->submit('/clinical/update?id='.$prescriptionId,correctionPayload($db,$prescriptionId,4))[0]===403,'cannot correct prescription after discharge');
$serviceEdit['CorrectionForm']['revision']=3;
check($doctor->submit('/admission-service/update?id='.$serviceLineId,$serviceEdit)[0]===403,'cannot correct service after discharge');
[$status,$html]=$discharge->request('/discharge/receipt?admission_id='.$ownId);
check($status===200 && str_contains($html,'شماره رسید') && str_contains($html,'مبلغ قطعی') && str_contains($html,'پانسمان') && !str_contains($html,'تشخیص آزمایشی') && !str_contains($html,'هر ۱۲ ساعت'),'financial receipt excludes medical instructions');
check($doctor->request('/discharge/receipt?admission_id='.$ownId)[0]===403,'doctor cannot access financial receipt route');
check($doctor->request('/record-change/index?type=prescription&id='.$prescriptionId)[0]===200,'history remains readable after discharge');
check($doctor->submit('/admission/ready?id='.$ownId,readyPayload($db,$ownId,'0'))[0]===403,'cannot revoke finalized discharge approval');
$frozen=$db->query('SELECT receipt_data FROM discharges WHERE admission_id='.$ownId)->fetchColumn();
check(is_array(json_decode($frozen,true)) && !str_contains($frozen,'هر ۱۲ ساعت') && !str_contains($frozen,'تشخیص آزمایشی'),'financial snapshot stores permitted receipt fields only');
$beforeReceipt=$discharge->request('/discharge/receipt?admission_id='.$ownId)[1];
$patientEdit['Patient']['last_name']='نام تغییرکرده پس از ترخیص';
check($secretary->submit('/patient/update?id='.$ownPatient,$patientEdit)[0]===302,'patient information remains editable after discharge');
check($admin->submit('/service/update?id=1',['Service'=>['title'=>'عنوان جدید ویزیت','price'=>120000,'active'=>0]])[0]===302,'admin changes service title and tariff after discharge');
[$status,$afterReceipt]=$discharge->request('/discharge/receipt?admission_id='.$ownId);
check($status===200 && str_contains($afterReceipt,'بیمار اول') && !str_contains($afterReceipt,'نام تغییرکرده پس از ترخیص') && !str_contains($afterReceipt,'عنوان جدید ویزیت'),'receipt preserves original patient identity and service titles');
check($db->query('SELECT receipt_data FROM discharges WHERE admission_id='.$ownId)->fetchColumn()===$frozen,'reference edits never rewrite frozen receipt');
$db->exec("UPDATE services SET title='ویزیت',price=100000,active=1 WHERE id=1");
check($secretary->submit('/admission/create?patient_id='.$ownPatient,['Admission'=>['doctor_id'=>$user['doctor_id'],'ward_id'=>1]])[0]===302,'new admission allowed after previous discharge');

[$status] = $admin->submit('/user/create?doctor_id=1', ['UserForm' => ['username'=>'test.existing','password'=>'StaffTest123!','password_repeat'=>'StaffTest123!','role'=>'discharge','full_name'=>'forged','doctor_id'=>$other['doctor_id']]]);
$linked = $db->query("SELECT u.role,d.id AS doctor_id,u.full_name FROM users u LEFT JOIN doctors d ON d.user_id=u.id WHERE u.username='test.existing'")->fetch(PDO::FETCH_ASSOC);
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
    'PrescriptionItem'=>[['medical_item_id'=>$supplyId,'kind'=>'supply','title'=>'سرم آزمایشی','quantity'=>1,'unit_price'=>1000]]]);
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
$changed = $db->query('SELECT u.role,d.id AS doctor_id FROM users u LEFT JOIN doctors d ON d.user_id=u.id WHERE u.id='.$user['id'])->fetch(PDO::FETCH_ASSOC);
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
$db->exec(file_get_contents(__DIR__.'/../database/upgrade_catalog_and_revisions.sql'));
$db->exec(file_get_contents(__DIR__.'/../database/upgrade_workflow_and_receipts.sql'));
check((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === $beforeUpgrade, 'admin schema upgrade is repeatable and preserves accounts');
check($db->query('SELECT receipt_data FROM discharges WHERE admission_id='.$ownId)->fetchColumn()===$frozen,'repeated workflow upgrade preserves frozen receipt');
// A failed insert rolls back database rows, but ActiveRecord objects can still hold assigned IDs.
$overflowCode = nationalCode('008150099');
check($secretary->submit('/patient/create', ['Patient' => [
    'first_name' => 'بیمار', 'last_name' => 'آزمون سقف مبلغ',
    'national_code' => $overflowCode, 'mobile' => '09121234567',
]])[0] === 302, 'create patient for overflow recovery regression');
$overflowPatient = (int) $db->query("SELECT id FROM patients WHERE national_code='$overflowCode'")->fetchColumn();
check($secretary->submit('/admission/create?patient_id='.$overflowPatient, ['Admission' => [
    'doctor_id' => $other['doctor_id'], 'ward_id' => 1,
]])[0] === 302, 'create separate active admission for overflow recovery');
$overflowAdmission = (int) $db->query('SELECT id FROM admissions WHERE patient_id='.$overflowPatient)->fetchColumn();
check($secondAdmin->submit('/medical-item/create', ['MedicalItem' => [
    'kind' => 'medicine', 'title' => 'قلم آزمون سقف مبلغ', 'unit_price' => '4294967295', 'active' => 1,
]])[0] === 302, 'create catalog item at maximum supported unit price');
$overflowItem = (int) $db->query("SELECT id FROM medical_items WHERE title='قلم آزمون سقف مبلغ'")->fetchColumn();
$overflowPayload = [
    'Prescription' => ['diagnosis' => 'آزمون اصلاح تعداد پس از خطای مبلغ'],
    'PrescriptionItem' => [[
        'medical_item_id' => $overflowItem, 'kind' => 'medicine', 'quantity' => '4294967295',
    ]],
];
[$status, $html] = $otherDoctor->submit('/clinical/create?admission_id='.$overflowAdmission, $overflowPayload);
check($status === 200 && str_contains($html, 'مجموع هزینه‌های پرونده از محدوده'), 'overflow returns a recoverable form error');
check((int) $db->query('SELECT COUNT(*) FROM prescriptions WHERE admission_id='.$overflowAdmission)->fetchColumn() === 0,
    'overflow rolls back the complete new prescription');
$document = new DOMDocument();
@$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
$fields = (new DOMXPath($document))->query('//input[@name="PrescriptionItem[0][item_id]"]');
check($fields->length === 1 && $fields->item(0)->getAttribute('value') === '',
    'returned new row never exposes an ID from a rolled-back insert');
$overflowPayload['PrescriptionItem'][0]['item_id'] = $fields->item(0)->getAttribute('value');
$overflowPayload['PrescriptionItem'][0]['quantity'] = '1';
check($otherDoctor->request('/clinical/create?admission_id='.$overflowAdmission, $overflowPayload)[0] === 302,
    'correcting quantity on the returned form succeeds without reloading');
check((int) $db->query('SELECT COUNT(*) FROM prescriptions WHERE admission_id='.$overflowAdmission)->fetchColumn() === 1,
    'overflow recovery creates exactly one final prescription');
$overflowPayload['PrescriptionItem'][0]['item_id'] = '99999999';
check($otherDoctor->request('/clinical/create?admission_id='.$overflowAdmission, $overflowPayload)[0] === 400,
    'a forged original row ID is still rejected after the recovery fix');

$existingAccountId = (int) $db->query("SELECT id FROM users WHERE username='test.existing'")->fetchColumn();
$rebind = ['UserForm' => [
    'username'=>'test.existing', 'full_name'=>'نام ارسالی', 'role'=>'doctor',
    'doctor_id'=>$user['doctor_id'], 'is_active'=>1, 'password'=>'', 'password_repeat'=>'',
]];
check($secondAdmin->submit('/user/update?id='.$existingAccountId, $rebind)[0] === 302,
    'administrator can move a physician account to an unclaimed profile');
check($db->query('SELECT user_id FROM doctors WHERE id=1')->fetchColumn() === null
    && (int) $db->query('SELECT user_id FROM doctors WHERE id='.$user['doctor_id'])->fetchColumn() === $existingAccountId,
    'rebinding releases the former profile and connects the selected one');
check($secondAdmin->submit('/user/update?id='.$existingAccountId, $rebind)[0] === 302
    && (int) $db->query('SELECT user_id FROM doctors WHERE id='.$user['doctor_id'])->fetchColumn() === $existingAccountId,
    'saving the same profile again preserves its account binding');
$reboundDoctor = new Browser($base);
check($reboundDoctor->submit('/site/login', ['LoginForm'=>['username'=>'test.existing','password'=>'StaffTest123!']])[0] === 302,
    'rebound doctor still logs in with the unchanged password');
check($reboundDoctor->request('/admission/view?id='.$ownId)[0] === 200
    && $reboundDoctor->request('/admission/view?id='.$otherId)[0] === 404,
    'rebound account sees only admissions of its new physician profile');
require __DIR__.'/../database/upgrade_doctor_user_link.php';
upgradeDoctorUserLink($db);
upgradeDoctorUserLink($db);
check((int) $db->query('SELECT user_id FROM doctors WHERE id='.$user['doctor_id'])->fetchColumn() === $existingAccountId,
    'link upgrade is also repeatable on the fresh schema');

echo "SUCCESS: $checks checks passed.\n";
