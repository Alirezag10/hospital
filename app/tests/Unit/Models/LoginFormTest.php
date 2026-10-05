<?php
namespace app\tests\Unit\Models;

use app\models\LoginForm;
use app\tests\Support\HospitalFixture;
use Yii;
use yii\base\Security;

final class LoginFormTest extends \Codeception\Test\Unit
{
    protected function _before(): void
    {
        HospitalFixture::reset();
        Yii::$app->user->logout(false);
    }

    protected function _after(): void
    {
        Yii::$app->user->logout(false);
    }

    public function testLoginNoUser(): void
    {
        $model = new LoginForm(new Security(), ['username' => 'missing-user', 'password' => 'wrong-password']);
        self::assertFalse($model->login());
        self::assertTrue(Yii::$app->user->isGuest);
    }

    public function testLoginWrongPassword(): void
    {
        $model = new LoginForm(new Security(), ['username' => 'operator', 'password' => 'wrong-password']);
        self::assertFalse($model->login());
        self::assertTrue($model->hasErrors('password'));
        self::assertTrue(Yii::$app->user->isGuest);
    }

    public function testLoginCorrect(): void
    {
        $model = new LoginForm(new Security(), ['username' => 'operator', 'password' => 'Hospital-test-password']);
        self::assertTrue($model->login());
        self::assertSame('operator', Yii::$app->user->identity->username);
        self::assertFalse($model->hasErrors());
    }
}
