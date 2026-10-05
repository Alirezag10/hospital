<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/** Admission with the doctor and ward names stored directly in the record. */
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
            [['patient_id', 'doctor_name', 'ward'], 'required'],
            [['patient_id'], 'integer', 'min' => 1],
            [['doctor_name', 'ward'], 'string', 'max' => 100],
            [['patient_id'], 'exist', 'targetClass' => Patient::class, 'targetAttribute' => ['patient_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه', 'patient_id' => 'بیمار', 'admission_date' => 'تاریخ پذیرش',
            'ward' => 'بخش', 'doctor_name' => 'نام پزشک', 'status' => 'وضعیت', 'created_at' => 'تاریخ ثبت',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
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

    public function getAdmissionServices()
    {
        return $this->hasMany(AdmissionService::class, ['admission_id' => 'id']);
    }

    public function getDischarge()
    {
        return $this->hasOne(Discharge::class, ['admission_id' => 'id']);
    }
}
