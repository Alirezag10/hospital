<?php
namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use app\tests\Support\HospitalFixture;

final class LoginCest
{
    public function _before(AcceptanceTester $I): void
    {
        HospitalFixture::reset();
    }

    public function loginAndReferenceNavigation(AcceptanceTester $I): void
    {
        $I->amOnPage('/index-test.php?r=site/login');
        $I->submitForm('#login-form', ['LoginForm[username]' => 'operator', 'LoginForm[password]' => 'Hospital-test-password']);
        $I->see('خروج (operator)');
        $I->seeLink('پزشکان');
        $I->click('پزشکان');
        $I->see('پزشکان', 'h1');
        $I->seeLink('تعریف پزشک');
        $I->click('بخش‌ها');
        $I->see('بخش‌ها', 'h1');
        $I->seeLink('تعریف بخش');
    }
}
