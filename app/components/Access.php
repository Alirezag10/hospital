<?php
namespace app\components;

use app\models\Admission;
use app\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\web\NotFoundHttpException;

final class Access
{
    public static function is(string $role): bool
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === $role;
    }

    public static function allows(string $route): bool
    {
        $route = ltrim($route, '/');
        if (in_array($route, ['site/login', 'site/error'], true)) { return true; }
        if (Yii::$app->user->isGuest) { return false; }
        $permissions = [
            'site/index' => [User::DOCTOR, User::SECRETARY, User::DISCHARGE],
            'site/logout' => [User::DOCTOR, User::SECRETARY, User::DISCHARGE],
            'admission/index' => [User::DOCTOR, User::SECRETARY, User::DISCHARGE],
            'admission/view' => [User::DOCTOR, User::SECRETARY, User::DISCHARGE],
            'admission/search-open-admissions' => [User::DOCTOR, User::DISCHARGE],
            'admission/calendar' => [User::SECRETARY],
            'admission/create' => [User::SECRETARY],
            'admission/search-patients' => [User::SECRETARY],
            'admission/search-doctors' => [User::SECRETARY],
            'patient/index' => [User::SECRETARY], 'patient/view' => [User::SECRETARY], 'patient/create' => [User::SECRETARY], 'patient/update' => [User::SECRETARY],
            'doctor/index' => [], 'doctor/create' => [], 'doctor/account' => [], 'doctor/update' => [],
            'ward/index' => [], 'ward/create' => [], 'ward/update' => [],
            'service/index' => [], 'service/create' => [], 'service/update' => [],
            'admission/ready' => [User::DOCTOR],
            'admission-service/create' => [User::DOCTOR],
            'clinical/create' => [User::DOCTOR], 'clinical/print' => [User::DOCTOR],
            'discharge/create' => [User::DISCHARGE],
            'user/index' => [], 'user/create' => [], 'user/update' => [],
            'medical-item/index' => [], 'medical-item/create' => [], 'medical-item/update' => [],
            'medical-item/search' => [User::DOCTOR],
            'clinical/update' => [User::DOCTOR], 'admission-service/update' => [User::DOCTOR],
            'record-change/index' => [User::DOCTOR],
            'discharge/receipt' => [User::DISCHARGE],
        ];
        return array_key_exists($route, $permissions)
            && (self::is(User::ADMIN) || in_array(Yii::$app->user->identity->role, $permissions[$route], true));
    }

    public static function admissions(?string $alias = null): ActiveQuery
    {
        $query = Admission::find();
        if ($alias !== null) { $query->alias($alias); }
        if (Yii::$app->user->isGuest) { return $query->andWhere('0=1'); }
        if (self::is(User::DOCTOR)) {
            $doctorId = Yii::$app->user->identity->doctor_id;
            return $doctorId === null ? $query->andWhere('0=1')
                : $query->andWhere([($alias ? $alias . '.' : '') . 'doctor_id' => $doctorId]);
        }
        return $query;
    }

    public static function admission($id): Admission
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $model = $id === false ? null : self::admissions()->with(['patient', 'discharge'])->andWhere(['id' => $id])->one();
        if ($model === null) { throw new NotFoundHttpException('پذیرش پیدا نشد یا به شما اختصاص ندارد.'); }
        return $model;
    }

    // Called inside a transaction by both treatment writers. Discharge locks the same row.
    public static function lockAdmission(int $id): array
    {
        $sql = 'SELECT id, status, doctor_id, ready_at, ready_by, treatment_version FROM admissions WHERE id = :id';
        $params = [':id' => $id];
        if (self::is(User::DOCTOR)) {
            $sql .= ' AND doctor_id = :doctor';
            $params[':doctor'] = Yii::$app->user->identity->doctor_id;
        }
        $row = Yii::$app->db->createCommand($sql . ' FOR UPDATE', $params)->queryOne();
        if ($row === false) { throw new NotFoundHttpException('پذیرش پیدا نشد یا به شما اختصاص ندارد.'); }
        return $row;
    }
}
