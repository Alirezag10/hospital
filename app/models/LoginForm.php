<?php
namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    public function rules()
    {
        return [
            [['username'], 'trim', 'skipOnArray' => true],
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 128],
            [['rememberMe'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return ['username' => 'نام کاربری', 'password' => 'رمز عبور', 'rememberMe' => 'مرا به خاطر بسپار'];
    }

    public function login(): bool
    {
        if (!$this->validate()) { return false; }
        $user = User::findOne(['username' => $this->username, 'is_active' => 1]);
        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError('password', 'نام کاربری یا رمز عبور درست نیست.');
            return false;
        }
        return Yii::$app->user->login($user, $this->rememberMe ? 14 * 86400 : 0);
    }
}
