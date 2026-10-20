<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\IntegrityException;
use yii\web\NotFoundHttpException;

class UserForm extends Model
{
    public $username;
    public $full_name;
    public $role = User::SECRETARY;
    public $is_active = 1;
    public $doctor_id;
    public $specialty;
    public $mobile;
    public $password = '';
    public $password_repeat = '';
    // The controller supplies the edit target. It is never a safe form attribute.
    public ?User $user = null;

    public function rules()
    {
        return [
            [['username', 'full_name', 'specialty'], 'trim', 'skipOnArray' => true],
            [['username', 'full_name', 'role', 'is_active'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 50],
            [['username'], 'match', 'pattern' => '/^[a-zA-Z0-9_.-]+$/D', 'message' => 'از حروف انگلیسی، عدد، نقطه یا زیرخط استفاده کنید.'],
            [['username'], 'validateUsername'],
            [['full_name', 'specialty'], 'string', 'max' => 100],
            [['role'], 'in', 'range' => array_keys(User::roleLabels())],
            [['is_active'], 'in', 'range' => [0, 1]],
            [['doctor_id'], 'default', 'value' => null],
            [['doctor_id'], 'integer', 'min' => 1],
            [['doctor_id'], 'validateDoctor'],
            [['specialty'], 'required', 'when' => fn() => $this->role === User::DOCTOR && !$this->doctor_id],
            [['mobile'], 'filter', 'filter' => [Patient::class, 'normalizeMobile'], 'skipOnArray' => true],
            [['mobile'], 'match', 'pattern' => Patient::MOBILE_PATTERN, 'message' => 'شماره موبایل معتبر وارد کنید.'],
            [['password'], 'required', 'when' => fn() => $this->user === null],
            [['password_repeat'], 'required', 'when' => fn() => $this->user === null || $this->password !== ''],
            [['password'], 'string', 'min' => 8, 'max' => 72, 'tooShort' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.'],
            [['password'], 'string', 'max' => 72, 'encoding' => '8bit', 'tooLong' => 'رمز کوتاه‌تری انتخاب کنید؛ حداکثر ۷۲ بایت مجاز است.'],
            [['password_repeat'], 'string', 'max' => 72],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => 'تکرار رمز عبور یکسان نیست.'],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) { return false; }
        if ($this->role !== User::DOCTOR) {
            $this->doctor_id = $this->specialty = $this->mobile = null;
        } elseif (filter_var($this->doctor_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
            $doctor = Doctor::findOne(['id' => $this->doctor_id]);
            if ($doctor !== null) { $this->full_name = $doctor->name; }
            $this->specialty = $this->mobile = null;
        }
        return true;
    }

    public function validateUsername($attribute)
    {
        if (User::find()->where(['username' => $this->username])->andWhere(['<>', 'id', $this->user?->id ?? 0])->exists()) {
            $this->addError($attribute, 'این نام کاربری قبلاً ثبت شده است.');
        }
    }

    public function validateDoctor($attribute)
    {
        if (!Doctor::find()->where(['id' => $this->doctor_id])->exists()) {
            $this->addError($attribute, 'پزشک انتخاب‌شده پیدا نشد.');
        } elseif (User::find()->where(['doctor_id' => $this->doctor_id])->andWhere(['<>', 'id', $this->user?->id ?? 0])->exists()) {
            $this->addError($attribute, 'این پزشک از قبل حساب دارد؛ همان حساب را ویرایش کنید.');
        }
    }

    public function attributeLabels()
    {
        return ['username' => 'نام کاربری', 'full_name' => 'نام و نام خانوادگی', 'role' => 'نقش', 'is_active' => 'وضعیت حساب',
            'doctor_id' => 'پزشک موجود', 'specialty' => 'تخصص', 'mobile' => 'موبایل', 'password' => 'رمز عبور', 'password_repeat' => 'تکرار رمز عبور'];
    }

    public function save(): ?User
    {
        if (!$this->validate()) { return null; }
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // Serializes administrator changes so two requests cannot remove the last admins together.
            $admins = $db->createCommand("SELECT id FROM users WHERE role = 'admin' AND is_active = 1 ORDER BY id FOR UPDATE")->queryColumn();
            $user = $this->user === null ? new User()
                : User::findBySql('SELECT * FROM users WHERE id = :id FOR UPDATE', [':id' => $this->user->id])->one();
            if ($user === null) { throw new NotFoundHttpException('کاربر پیدا نشد.'); }
            if (!$user->isNewRecord && $user->role === User::ADMIN && (int) $user->is_active === 1
                && ($this->role !== User::ADMIN || (int) $this->is_active !== 1) && count($admins) <= 1) {
                $this->addError($this->role !== User::ADMIN ? 'role' : 'is_active', 'آخرین مدیر فعال را نمی‌توان غیرفعال کرد یا نقش او را تغییر داد.');
                $transaction->rollBack();
                return null;
            }
            $doctor = null;
            if ($this->role === User::DOCTOR) {
                $doctor = $this->doctor_id ? Doctor::findOne(['id' => $this->doctor_id])
                    : new Doctor(['name' => $this->full_name, 'specialty' => $this->specialty, 'mobile' => $this->mobile]);
                if ($doctor === null) { throw new NotFoundHttpException('پزشک انتخاب‌شده پیدا نشد.'); }
                if ($doctor->isNewRecord && !$doctor->save()) {
                    foreach ($doctor->getFirstErrors() as $attribute => $error) { $this->addError($attribute === 'name' ? 'full_name' : $attribute, $error); }
                    $transaction->rollBack();
                    return null;
                }
            }
            $rotateKey = $user->isNewRecord || $this->password !== '' || $user->role !== $this->role
                || (int) $user->is_active !== (int) $this->is_active || (string) $user->doctor_id !== (string) $doctor?->id;
            $user->username = $this->username;
            $user->full_name = $doctor?->name ?? $this->full_name;
            $user->role = $this->role;
            $user->is_active = (int) $this->is_active;
            $user->doctor_id = $doctor?->id;
            if ($rotateKey) { $user->auth_key = Yii::$app->security->generateRandomString(); }
            if ($this->password !== '') { $user->password_hash = Yii::$app->security->generatePasswordHash($this->password); }
            $user->save(false);
            $transaction->commit();
            return $user;
        } catch (IntegrityException $error) {
            $transaction->rollBack();
            $this->addError('username', 'نام کاربری یا حساب این پزشک قبلاً ثبت شده است.');
            return null;
        } catch (\Throwable $error) {
            if ($transaction->isActive) { $transaction->rollBack(); }
            throw $error;
        }
    }
}
