<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Security;

class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;

    private ?User $_user = null;

    private bool $_userLoaded = false;

    public function __construct(
        private readonly Security $security,
        $config = []
    ) {
        parent::__construct($config);
    }


    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }


    public function validatePassword(
        string $attribute,
        array|null $params
    ): void {
        if (!$this->hasErrors()) {

            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError(
                    $attribute,
                    'نام کاربری یا رمز عبور اشتباه است.'
                );
            }
        }
    }


    public function login(): bool
    {
        if ($this->validate()) {

            return Yii::$app->user->login(
                $this->getUser(),
                $this->rememberMe ? 3600 * 24 * 30 : 0
            );
        }

        return false;
    }


    public function getUser(): ?User
    {
        if (!$this->_userLoaded) {

            $this->_user = User::findByUsername(
                $this->username
            );

            $this->_userLoaded = true;
        }

        return $this->_user;
    }
}