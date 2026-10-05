<?php
namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use app\tests\Support\HospitalFixture;

final class HomeCest
{
    public function _before(AcceptanceTester $I): void
    {
        HospitalFixture::reset();
    }

    public function guestDashboard(AcceptanceTester $I): void
    {
        $I->amOnPage('/index-test.php?r=site/index');
        $I->see('داشبورد بیمارستان', 'h1');
        $I->seeLink('ورود');
        $I->dontSeeLink('ثبت پذیرش');
    }
}
