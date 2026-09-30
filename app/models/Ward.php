<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wards".
 *
 * @property int $id
 * @property string $name
 * @property string|null $floor
 * @property int|null $capacity
 * @property string $created_at
 */
class Ward extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['floor'], 'default', 'value' => null],
            [['capacity'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['capacity'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['floor'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'floor' => 'Floor',
            'capacity' => 'Capacity',
            'created_at' => 'Created At',
        ];
    }

}
