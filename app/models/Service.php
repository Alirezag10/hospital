<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "services".
 *
 * @property int $id
 * @property string $title
 * @property int $price
 * @property int $active
 *
 * @property AdmissionService[] $admissionServices
 */
class Service extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'default', 'value' => 1],
            [['title', 'price'], 'required'],
            [['price', 'active'], 'integer'],
            [['title'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'title' => 'عنوان خدمت',
            'price' => 'قیمت',
            'active' => 'فعال',
            'created_at' => 'تاریخ ثبت',
        ];
    }

    /**
     * Gets query for [[AdmissionServices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmissionServices()
    {
        return $this->hasMany(AdmissionService::class, ['service_id' => 'id']);
    }

}
