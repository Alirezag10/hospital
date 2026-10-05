<?php
namespace app\tests\Unit\Models;

use app\models\User;
use app\tests\Support\HospitalFixture;

final class UserTest extends \Codeception\Test\Unit
{
    protected function _before(): void
    {
        HospitalFixture::reset();
    }

    public function testIdentityAndUsername(): void
    {
        self::assertSame('operator', User::findIdentity(100)->username);
        self::assertNull(User::findIdentity(999));
        self::assertSame(100, (int) User::findByUsername('operator')->id);
        self::assertNull(User::findByUsername('missing-user'));
    }

    public function testAccessTokenAndAuthKey(): void
    {
        $user = User::findIdentityByAccessToken('hospital-test-token');
        self::assertSame('operator', $user->username);
        self::assertTrue($user->validateAuthKey('hospital-test-auth-key'));
        self::assertFalse($user->validateAuthKey('wrong-key'));
        self::assertNull(User::findIdentityByAccessToken('missing-token'));
    }

    public function testPasswordHash(): void
    {
        $user = User::findByUsername('operator');
        self::assertTrue($user->validatePassword('Hospital-test-password'));
        self::assertFalse($user->validatePassword('wrong-password'));
        self::assertNotSame('Hospital-test-password', $user->password_hash);
    }
}
