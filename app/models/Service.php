<?php

namespace app\models;

/**
 * Model for the "services" table.
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
    public static function tableName()
    {
        return 'services';
    }

    public function rules()
    {
        return [
            [['active'], 'default', 'value' => 1],
            [['title', 'price'], 'required'],
            [['price'], 'integer', 'min' => 0],
            [['active'], 'in', 'range' => [0, 1]],
            [['title'], 'string', 'max' => 150],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'title' => 'عنوان خدمت',
            'price' => 'قیمت',
            'active' => 'فعال',
        ];
    }

    public function getAdmissionServices()
    {
        return $this->hasMany(
            AdmissionService::class,
            ['service_id' => 'id']
        );
    }
}