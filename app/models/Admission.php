<?php

namespace app\models;

/**
 * Model for the "admissions" table.
 *
 * @property int $id
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property int|null $ward_id
 * @property string $admission_date
 * @property string $ward
 * @property string $doctor_name
 * @property string $status
 * @property string $created_at
 *
 * @property Patient $patient
 * @property Doctor|null $doctor
 * @property Ward|null $wardModel
 * @property AdmissionService[] $admissionServices
 * @property Discharge|null $discharge
 */
class Admission extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'admissions';
    }

    public function rules()
    {
        return [
            [
                ['status'],
                'default',
                'value' => 'admitted',
            ],
            [
                ['patient_id', 'doctor_id', 'ward_id'],
                'required',
            ],
            [
                ['patient_id', 'doctor_id', 'ward_id'],
                'integer',
            ],
            [
                ['admission_date'],
                'trim',
            ],
            [
                ['admission_date'],
                'required',
                'when' => static function ($model) {
                    return !$model->isNewRecord;
                },
                'enableClientValidation' => false,
                'message' => 'تاریخ پذیرش را وارد کنید.',
            ],
            [
                ['admission_date'],
                'date',
                'format' => 'php:Y-m-d H:i:s',
                'message' => 'تاریخ و ساعت معتبر از تقویم انتخاب کنید.',
            ],
            [
                ['status'],
                'in',
                'range' => ['admitted', 'discharged'],
            ],
            [
                ['patient_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Patient::class,
                'targetAttribute' => ['patient_id' => 'id'],
            ],
            [
                ['doctor_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Doctor::class,
                'targetAttribute' => ['doctor_id' => 'id'],
            ],
            [
                ['ward_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Ward::class,
                'targetAttribute' => ['ward_id' => 'id'],
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'patient_id' => 'بیمار',
            'doctor_id' => 'پزشک',
            'ward_id' => 'بخش',
            'admission_date' => 'تاریخ پذیرش',
            'ward' => 'بخش',
            'doctor_name' => 'نام پزشک',
            'status' => 'وضعیت',
            'created_at' => 'تاریخ ثبت',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // خواندن رکوردهای انتخاب‌شده، حتی پس از تغییر شناسه‌ها در ویرایش.
        $doctor = Doctor::findOne($this->doctor_id);
        $ward = Ward::findOne($this->ward_id);

        if ($doctor === null) {
            $this->addError('doctor_id', 'پزشک انتخاب‌شده پیدا نشد.');
            return false;
        }

        if ($ward === null) {
            $this->addError('ward_id', 'بخش انتخاب‌شده پیدا نشد.');
            return false;
        }

        // سازگاری با ستون‌های قدیمی جدول.
        $this->doctor_name = $doctor->name;
        $this->ward = $ward->name;

        if ($insert) {
            if ($this->admission_date === null || $this->admission_date === '') {
                $this->admission_date = date('Y-m-d H:i:s');
            }

            if ($this->created_at === null || $this->created_at === '') {
                $this->created_at = date('Y-m-d H:i:s');
            }
        }

        return true;
    }

    public function getPatient()
    {
        return $this->hasOne(Patient::class, ['id' => 'patient_id']);
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
        return $this->hasMany(
            AdmissionService::class,
            ['admission_id' => 'id']
        );
    }

    public function getDischarge()
    {
        return $this->hasOne(
            Discharge::class,
            ['admission_id' => 'id']
        );
    }
}