<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'users';
    }


    public static function findIdentity($id): ?static
    {
        return static::findOne($id);
    }


    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return static::findOne(['access_token' => $token]);
    }


    public static function findByUsername(string $username): ?static
    {
        return static::findOne(['username' => $username]);
    }


    public function getId(): int|string
    {
        return $this->id;
    }


    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }


    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }


    public function validatePassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }


    public function rules()
    {
        return [
            [['username', 'password_hash'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['password_hash'], 'string', 'max' => 255],
            [['role'], 'string', 'max' => 20],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'username' => 'نام کاربری',
            'password_hash' => 'رمز عبور',
            'role' => 'نقش',
            'created_at' => 'تاریخ ثبت',
        ];
    }
}
