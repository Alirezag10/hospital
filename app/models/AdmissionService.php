<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "admission_services".
 *
 * @property int $id
 * @property int $admission_id
 * @property int $service_id
 * @property int $quantity
 * @property int $unit_price
 * @property string $created_at
 *
 * @property Admission $admission
 * @property Service $service
 */
class AdmissionService extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admission_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity'], 'default', 'value' => 1],
            [['admission_id', 'service_id', 'unit_price'], 'required'],
            [['admission_id', 'service_id', 'unit_price'], 'integer'],
            [['quantity'], 'integer', 'min' => 1],
            [['admission_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Admission::class,
                'targetAttribute' => ['admission_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Service::class,
                'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admission_id' => 'Admission ID',
            'service_id' => 'Service ID',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'created_at' => 'Created At',
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

    /**
     * Gets query for [[Service]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::class, ['id' => 'service_id']);
    }

}
