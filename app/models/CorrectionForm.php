<?php
namespace app\models;

class CorrectionForm extends \yii\base\Model
{
    public $reason;
    public $revision;
    public function rules()
    {
        return [[['reason'],'trim','skipOnArray'=>true],[['reason','revision'],'required'],
            [['reason'],'string','max'=>500],[['revision'],'integer','min'=>1]];
    }
    public function attributeLabels() { return ['reason'=>'دلیل اصلاح','revision'=>'نسخه رکورد']; }
}
