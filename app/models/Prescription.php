<?php
namespace app\models;

class Prescription extends \yii\db\ActiveRecord
{
    public static function tableName() { return 'prescriptions'; }
    public function rules()
    {
        return [
            [['diagnosis', 'notes'], 'trim', 'skipOnArray' => true],
            [['diagnosis'], 'required'],
            [['diagnosis'], 'string', 'max' => 2000],
            [['notes'], 'string', 'max' => 5000],
        ];
    }
    public function attributeLabels() { return ['diagnosis' => 'تشخیص و شرح وضعیت', 'notes' => 'توصیه‌ها و دستورات پزشک']; }
    public function getItems() { return $this->hasMany(PrescriptionItem::class, ['prescription_id' => 'id']); }
    public function getDoctor() { return $this->hasOne(Doctor::class, ['id' => 'doctor_id']); }
    public function getAuthor() { return $this->hasOne(User::class, ['id' => 'user_id']); }
    public function getAdmission() { return $this->hasOne(Admission::class, ['id' => 'admission_id']); }
}
