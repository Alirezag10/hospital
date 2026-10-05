<?php
namespace app\tests\Functional;

use app\models\Admission;
use app\models\AdmissionService;
use app\models\Discharge;
use app\models\Patient;
use app\tests\Support\FunctionalTester;
use app\tests\Support\HospitalFixture;
use Yii;

final class HospitalWorkflowCest
{
    public function _before(FunctionalTester $I): void
    {
        $I->amLoggedInAs(HospitalFixture::reset());
    }

    private function admission(): Admission
    {
        $model = new Admission(['patient_id' => 1, 'doctor_id' => 1, 'ward_id' => 1, 'status' => 'admitted']);
        if (!$model->save()) {
            throw new \RuntimeException(json_encode($model->errors, JSON_UNESCAPED_UNICODE));
        }
        return $model;
    }

    public function completePatientToDischargeFlow(FunctionalTester $I): void
    {
        $I->amOnRoute('patient/create');
        $I->submitForm('form', [
            'Patient[first_name]' => 'علی', 'Patient[last_name]' => 'نمونه',
            'Patient[national_code]' => '0098765432', 'Patient[mobile]' => '09129876543',
        ]);
        $patient = Patient::findOne(['national_code' => '0098765432']);
        $I->assertNotNull($patient);
        $I->click('ثبت پذیرش برای این بیمار');
        $I->seeOptionIsSelected('#admission-patient_id', 'علی نمونه — کد ملی: 0098765432');
        $I->submitForm('form', ['Admission[doctor_id]' => 1, 'Admission[ward_id]' => 1]);
        $admission = Admission::findOne(['patient_id' => $patient->id]);
        $I->assertNotNull($admission);
        $I->click('افزودن خدمت');
        $I->seeOptionIsSelected('#admissionservice-admission_id', '#' . $admission->id . ' — علی نمونه');
        $I->submitForm('form', ['AdmissionService[service_id]' => 1, 'AdmissionService[quantity]' => 2]);
        $I->see('خلاصه پرونده پذیرش #' . $admission->id, 'h1');
        $I->see('200,000 تومان');
        $I->click('مشاهده هزینه و ترخیص');
        $I->see('200,000', '#discharge-preview-total');
        $I->see('ویزیت', '#discharge-cost-preview');
        $I->submitForm('#discharge-form', ['Discharge[description]' => 'پایان درمان']);
        $I->see('ترخیص‌شده');
        $I->see('پایان درمان');
        $I->assertSame('discharged', Admission::findOne($admission->id)->status);
        $I->assertSame(200000, (int) Discharge::findOne(['admission_id' => $admission->id])->total_amount);
        $I->dontSeeLink('افزودن خدمت');
        $I->dontSeeLink('مشاهده هزینه و ترخیص');
    }

    public function patientListLinksAndSearch(FunctionalTester $I): void
    {
        $I->amOnRoute('patient/index');
        $I->seeLink('ثبت پذیرش', '/index-test.php?r=admission%2Fcreate&patient_id=1');
        $I->submitForm('form', ['PatientSearch[first_name]' => 'بیمار']);
        $I->see('آزمایشی', 'td');
        $I->submitForm('form', ['PatientSearch[national_code]' => '0012345678']);
        $I->see('بیمار', 'td');
        $I->submitForm('form', ['PatientSearch[national_code]' => 'no-match']);
        $I->see('بیماری مطابق جست‌وجوی شما پیدا نشد.');
    }

    public function historicalPriceAndServerCalculatedTotal(FunctionalTester $I): void
    {
        $admission = $this->admission();
        $I->amOnRoute('admission-service/create', ['admission_id' => $admission->id]);
        $I->submitForm('form', ['AdmissionService[service_id]' => 1, 'AdmissionService[quantity]' => 2, 'AdmissionService[unit_price]' => 1]);
        $item = AdmissionService::findOne(['admission_id' => $admission->id]);
        $I->assertSame(100000, (int) $item->unit_price);
        Yii::$app->db->createCommand()->update('services', ['price' => 900000], ['id' => 1])->execute();
        $I->amOnRoute('admission-service/update', ['id' => $item->id]);
        $I->submitForm('form', ['AdmissionService[quantity]' => 3]);
        $I->assertSame(100000, (int) AdmissionService::findOne($item->id)->unit_price);
        $I->amOnRoute('discharge/create');
        $I->submitForm('#discharge-preview-form', ['admission_id' => $admission->id]);
        $I->see('300,000', '#discharge-preview-total');
        $I->submitForm('#discharge-form', ['Discharge[total_amount]' => 1, 'Discharge[discharge_date]' => '1900-01-01']);
        $discharge = Discharge::findOne(['admission_id' => $admission->id]);
        $I->assertSame(300000, (int) $discharge->total_amount);
        $I->assertNotEquals('1900-01-01', $discharge->discharge_date);
    }

    public function staleFormsAndDuplicateDischargeAreRejected(FunctionalTester $I): void
    {
        $admission = $this->admission();
        $I->amOnRoute('admission-service/create', ['admission_id' => $admission->id]);
        $I->submitForm('form', ['AdmissionService[service_id]' => 1, 'AdmissionService[quantity]' => 1]);
        $item = AdmissionService::findOne(['admission_id' => $admission->id]);
        $I->amOnRoute('discharge/create', ['admission_id' => $admission->id]);
        $I->submitForm('#discharge-form', []);
        $I->sendAjaxPostRequest('/index.php?r=discharge/create', ['Discharge' => ['admission_id' => $admission->id]]);
        $I->assertSame(1, (int) Discharge::find()->where(['admission_id' => $admission->id])->count());
        $I->sendAjaxPostRequest('/index.php?r=admission-service/create', ['AdmissionService' => ['admission_id' => $admission->id, 'service_id' => 1, 'quantity' => 9]]);
        $I->assertSame(1, (int) AdmissionService::find()->where(['admission_id' => $admission->id])->count());
        $I->sendAjaxPostRequest('/index.php?r=admission-service/update&id=' . $item->id, ['AdmissionService' => ['quantity' => 9]]);
        $I->assertSame(1, (int) AdmissionService::findOne($item->id)->quantity);
        $I->sendAjaxPostRequest('/index.php?r=admission/update&id=' . $admission->id, ['Admission' => ['status' => 'admitted']]);
        $I->seeResponseCodeIs(403);
        $I->assertSame('discharged', Admission::findOne($admission->id)->status);
    }

    public function missingReferenceDataHasAnActionableMessage(FunctionalTester $I): void
    {
        Yii::$app->db->createCommand()->delete('doctors')->execute();
        Yii::$app->db->createCommand()->delete('wards')->execute();
        $I->amOnRoute('admission/create', ['patient_id' => 1]);
        $I->see('پیش از ثبت پذیرش، حداقل یک پزشک و یک بخش تعریف کنید.');
        $I->seeLink('تعریف پزشک');
        $I->seeLink('تعریف بخش');
        $I->seeElement('button[type=submit][disabled]');
        $I->seeLink('پزشکان');
        $I->seeLink('بخش‌ها');
    }

    public function invalidContextDoesNotSelectAnotherRecord(FunctionalTester $I): void
    {
        $I->amOnRoute('admission/create', ['patient_id' => '1invalid']);
        $I->seeResponseCodeIs(404);
        $I->amOnRoute('admission-service/create', ['admission_id' => 999999]);
        $I->seeResponseCodeIs(404);
        $I->amOnRoute('discharge/create', ['admission_id' => 999999]);
        $I->seeResponseCodeIs(404);
    }

    public function dashboardAndAdmissionStatusesReflectDischarge(FunctionalTester $I): void
    {
        $admission = $this->admission();
        $I->amOnRoute('site/index');
        $I->see('1', '.row .col-md-4:nth-child(1) .display-6');
        $I->see('1', '.row .col-md-4:nth-child(2) .display-6');
        $I->see('0', '.row .col-md-4:nth-child(3) .display-6');
        $I->amOnRoute('admission/index');
        $I->see('بستری', 'td');
        $I->amOnRoute('discharge/create', ['admission_id' => $admission->id]);
        $I->see('0', '#discharge-preview-total');
        $I->submitForm('#discharge-form', []);
        $I->amOnRoute('admission/index');
        $I->see('ترخیص‌شده', 'td');
        $I->amOnRoute('site/index');
        $I->see('0', '.row .col-md-4:nth-child(2) .display-6');
        $I->see('1', '.row .col-md-4:nth-child(3) .display-6');
    }
}
