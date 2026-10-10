<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/** Admission keeps selected IDs plus historical doctor and ward names. */
class Admission extends ActiveRecord
{
    public static function tableName()
    {
        return 'admissions';
    }

    public function rules()
    {
        return [
            [['doctor_name', 'ward'], 'trim'],
            [['patient_id', 'doctor_id', 'ward_id'], 'required'],
            [['patient_id', 'doctor_id', 'ward_id'], 'integer', 'min' => 1],
            [['doctor_name', 'ward'], 'string', 'max' => 100],
            [['patient_id'], 'exist', 'targetClass' => Patient::class, 'targetAttribute' => ['patient_id' => 'id']],
            [['doctor_id'], 'exist', 'targetClass' => Doctor::class, 'targetAttribute' => ['doctor_id' => 'id'], 'message' => 'پزشک انتخاب‌شده پیدا نشد.'],
            [['ward_id'], 'exist', 'targetClass' => Ward::class, 'targetAttribute' => ['ward_id' => 'id'], 'message' => 'بخش انتخاب‌شده پیدا نشد.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه', 'patient_id' => 'بیمار', 'doctor_id' => 'پزشک', 'ward_id' => 'بخش', 'admission_date' => 'تاریخ پذیرش',
            'ward' => 'بخش', 'doctor_name' => 'نام پزشک', 'status' => 'وضعیت', 'created_at' => 'تاریخ ثبت',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($scenarios as &$attributes) {
            foreach ($attributes as &$attribute) {
                if (in_array($attribute, ['doctor_name', 'ward'], true)) {
                    $attribute = '!' . $attribute;
                }
            }
        }
        return $scenarios;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $doctor = Doctor::findOne(['id' => $this->doctor_id]);
            $ward = Ward::findOne(['id' => $this->ward_id]);
            if ($doctor === null || $ward === null) {
                $this->addError('doctor_id', 'پزشک یا بخش انتخاب‌شده دیگر موجود نیست؛ دوباره انتخاب کنید.');
                return false;
            }
            $this->doctor_name = $doctor->name;
            $this->ward = $ward->name;
            $now = Yii::$app->db->createCommand('SELECT NOW()')->queryScalar();
            $this->status = 'admitted';
            $this->admission_date = $now;
            $this->created_at = $now;
        }
        return true;
    }

    public function getPatient()
    {
        return $this->hasOne(Patient::class, ['id' => 'patient_id']);
    }

    public function getSelectionLabel(): string
    {
        $patient = $this->patient;
        if ($patient === null) {
            return 'بیمار نامشخص';
        }

        return $patient->first_name . ' ' . $patient->last_name
            . ' (کد ملی: ' . $patient->national_code . ')';
    }

    public function getDoctor()
    {
        return $this->hasOne(Doctor::class, ['id' => 'doctor_id']);
    }

    public function getWardModel()
    {
        return $this->hasOne(Ward::class, ['id' => 'ward_id']);
    }

    public function getAdmissionServices()
    {
        return $this->hasMany(AdmissionService::class, ['admission_id' => 'id']);
    }

    public function getDischarge()
    {
        return $this->hasOne(Discharge::class, ['admission_id' => 'id']);
    }
}
