<?php

namespace app\tests\Support;

use app\models\User;
use Yii;

/** Small, repeatable data set used only by the isolated hospital test database. */
final class HospitalFixture
{
    public static function reset(): User
    {
        $db = Yii::$app->db;
        if (!YII_ENV_TEST || !preg_match('/;dbname=hospital_[a-zA-Z0-9_]*test(?:;|$)/', $db->dsn)) {
            throw new \RuntimeException('Refusing to reset a non-test database.');
        }
        $transaction = $db->beginTransaction();
        try {
            foreach (['discharges', 'admission_services', 'admissions', 'patients', 'doctors', 'wards', 'services', 'users'] as $table) {
                $db->createCommand()->delete($table)->execute();
            }
            $db->createCommand()->insert('users', [
                'id' => 100, 'username' => 'operator',
                'password_hash' => password_hash('Hospital-test-password', PASSWORD_DEFAULT),
                'auth_key' => 'hospital-test-auth-key', 'access_token' => 'hospital-test-token', 'role' => 'operator',
            ])->execute();
            $db->createCommand()->insert('patients', [
                'id' => 1, 'first_name' => 'بیمار', 'last_name' => 'آزمایشی',
                'national_code' => '0012345678', 'mobile' => '09123456789',
            ])->execute();
            $db->createCommand()->insert('doctors', ['id' => 1, 'name' => 'پزشک آزمایشی', 'specialty' => 'عمومی'])->execute();
            $db->createCommand()->insert('wards', ['id' => 1, 'name' => 'بخش آزمایشی', 'capacity' => 10])->execute();
            $db->createCommand()->insert('services', ['id' => 1, 'title' => 'ویزیت', 'price' => 100000, 'active' => 1])->execute();
            $transaction->commit();
        } catch (\Throwable $error) {
            $transaction->rollBack();
            throw $error;
        }
        return User::findIdentity(100);
    }
}
