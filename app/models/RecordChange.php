<?php
namespace app\models;

class RecordChange extends \yii\db\ActiveRecord
{
    public static function tableName() { return 'record_changes'; }
    public function getActor() { return $this->hasOne(User::class, ['id'=>'actor_id']); }
    public static function record(int $admissionId, string $type, int $entityId, string $reason, array $before, array $after): void
    {
        $change = new self([
            'admission_id'=>$admissionId,'entity_type'=>$type,'entity_id'=>$entityId,
            'actor_id'=>\Yii::$app->user->id,'reason'=>$reason,
            'before_data'=>json_encode($before,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),
            'after_data'=>json_encode($after,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),
        ]);
        $change->save(false);
    }
}
