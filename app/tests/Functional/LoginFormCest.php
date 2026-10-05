<?php
namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;
use app\tests\Support\HospitalFixture;

final class LoginFormCest
{
    public function _before(FunctionalTester $I): void
    {
        HospitalFixture::reset();
        $I->amOnRoute('site/login');
    }

    public function loginWithWrongCredentials(FunctionalTester $I): void
    {
        $I->see('ورود به سامانه', 'h1');
        $I->submitForm('#login-form', ['LoginForm[username]' => 'operator', 'LoginForm[password]' => 'wrong']);
        $I->see('نام کاربری یا رمز عبور اشتباه است.');
        $I->seeElement('#login-form');
    }

    public function loginAndLogout(FunctionalTester $I): void
    {
        $I->submitForm('#login-form', ['LoginForm[username]' => 'operator', 'LoginForm[password]' => 'Hospital-test-password']);
        $I->see('خروج (operator)');
        $I->see('داشبورد بیمارستان', 'h1');
        $I->sendAjaxPostRequest('/index.php?r=site/logout');
        $I->amOnRoute('site/index');
        $I->see('ورود به سامانه');
        $I->dontSee('خروج (operator)');
    }

    public function operationalPagesRequireLogin(FunctionalTester $I): void
    {
        $I->amOnRoute('patient/index');
        $I->seeElement('#login-form');
        $I->dontSee('ثبت بیمار جدید');
    }
}
