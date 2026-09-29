<?php

namespace app\models;

/**
 * Model for the "admissions" table.
 *
 * @property int $id
 * @property int $patient_id
 * @property string $admission_date
 * @property string $ward
 * @property string $doctor_name
 * @property string $status
 * @property string $created_at
 *
 * @property AdmissionService[] $admissionServices
 * @property Discharge|null $discharge
 * @property Patient $patient
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
            [['status'], 'default', 'value' => 'admitted'],
            [['patient_id', 'ward', 'doctor_name'], 'required'],
            [['patient_id'], 'integer'],
            [['ward', 'doctor_name'], 'string', 'max' => 100],
            [['status'], 'in', 'range' => ['admitted', 'discharged']],
            [['patient_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Patient::class,
                'targetAttribute' => ['patient_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'patient_id' => 'بیمار',
            'admission_date' => 'تاریخ پذیرش',
            'ward' => 'بخش',
            'doctor_name' => 'نام پزشک',
            'status' => 'وضعیت',
            'created_at' => 'تاریخ ثبت',
        ];
    }

    public function getAdmissionServices()
    {
        return $this->hasMany(AdmissionService::class, ['admission_id' => 'id']);
    }

    public function getDischarge()
    {
        return $this->hasOne(Discharge::class, ['admission_id' => 'id']);
    }

    public function getPatient()
    {
        return $this->hasOne(Patient::class, ['id' => 'patient_id']);
    }
}