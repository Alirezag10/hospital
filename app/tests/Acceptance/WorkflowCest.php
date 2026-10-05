<?php

namespace app\tests\Acceptance;

use app\models\Admission;
use app\models\AdmissionService;
use app\models\Discharge;
use app\tests\Support\AcceptanceTester;
use app\tests\Support\HospitalFixture;

final class WorkflowCest
{
    public function _before(AcceptanceTester $I): void
    {
        HospitalFixture::reset();
    }

    public function admissionServicesAndDischargeOverHttp(AcceptanceTester $I): void
    {
        $I->amOnPage('/index-test.php?r=site/login');
        $I->submitForm('#login-form', ['LoginForm[username]' => 'operator', 'LoginForm[password]' => 'Hospital-test-password']);
        $I->click('بیماران');
        $I->click('ثبت پذیرش');
        $I->seeOptionIsSelected('#admission-patient_id', 'بیمار آزمایشی — کد ملی: 0012345678');
        $I->submitForm('form', ['Admission[doctor_id]' => 1, 'Admission[ward_id]' => 1]);
        $admission = Admission::findOne(['patient_id' => 1]);
        $I->assertNotNull($admission);
        $I->click('افزودن خدمت');
        $I->submitForm('form', ['AdmissionService[service_id]' => 1, 'AdmissionService[quantity]' => 2]);
        $I->see('200,000 تومان');
        $I->click('مشاهده هزینه و ترخیص');
        $I->see('200,000', '#discharge-preview-total');
        $I->see('ویزیت', '#discharge-cost-preview');
        $I->submitForm('#discharge-form', ['Discharge[description]' => 'ترخیص آزمایشی HTTP']);
        $I->see('ترخیص‌شده');
        $I->assertSame('discharged', Admission::findOne($admission->id)->status);
        $I->assertSame(200000, (int) Discharge::findOne(['admission_id' => $admission->id])->total_amount);
        $I->assertSame(100000, (int) AdmissionService::findOne(['admission_id' => $admission->id])->unit_price);
        $I->dontSeeLink('افزودن خدمت');
    }
}
