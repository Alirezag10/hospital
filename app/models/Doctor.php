<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "doctors".
 *
 * @property int $id
 * @property string $name
 * @property string $specialty
 * @property string|null $mobile
 * @property string $created_at
 */
class Doctor extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'doctors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mobile'], 'default', 'value' => null],
            [['name', 'specialty'], 'required'],
            [['created_at'], 'safe'],
            [['name', 'specialty'], 'string', 'max' => 100],
            [['mobile'], 'string', 'max' => 20],
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
            'specialty' => 'Specialty',
            'mobile' => 'Mobile',
            'created_at' => 'Created At',
        ];
    }

}
