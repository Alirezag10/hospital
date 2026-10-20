<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public const ADMIN = 'admin';
    public const DOCTOR = 'doctor';
    public const SECRETARY = 'secretary';
    public const DISCHARGE = 'discharge';

    public static function tableName() { return 'users'; }

    public static function roleLabels(): array
    {
        return [self::ADMIN => 'مدیر سیستم', self::SECRETARY => 'منشی', self::DOCTOR => 'پزشک', self::DISCHARGE => 'ترخیص‌کار'];
    }

    public function getRoleLabel(): string { return self::roleLabels()[$this->role] ?? ''; }
    public static function findIdentity($id) { return static::findOne(['id' => $id, 'is_active' => 1]); }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return $this->id; }
    public function getAuthKey() { return $this->auth_key; }
    public function validateAuthKey($authKey) { return is_string($authKey) && hash_equals($this->auth_key, $authKey); }
    public function validatePassword(string $password): bool { return Yii::$app->security->validatePassword($password, $this->password_hash); }
    public function getDoctor() { return $this->hasOne(Doctor::class, ['id' => 'doctor_id']); }
}
