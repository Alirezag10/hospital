<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "discharges".
 *
 * @property int $id
 * @property int $admission_id
 * @property string $discharge_date
 * @property string|null $description
 * @property int $total_amount
 * @property string $created_at
 *
 * @property Admission $admission
 */
class Discharge extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'discharges';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['admission_id', 'discharge_date', 'total_amount'], 'required'],
            [['admission_id'], 'integer'],
            [['total_amount'], 'integer', 'min' => 0],
            [['description'], 'string'],
            [['admission_id'], 'unique'],
            [['admission_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Admission::class,
                'targetAttribute' => ['admission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'admission_id' => 'پذیرش',
            'discharge_date' => 'تاریخ ترخیص',
            'description' => 'توضیحات',
            'total_amount' => 'مبلغ کل',
            'created_at' => 'تاریخ ثبت',
        ];
    }

    /**
     * Gets query for [[Admission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmission()
    {
        return $this->hasOne(Admission::class, ['id' => 'admission_id']);
    }

}
