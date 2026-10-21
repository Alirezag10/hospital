<?php

namespace app\components;

use app\models\Discharge;
use yii\db\ActiveQuery;

final class DischargeQueue
{
    public static function query(): ActiveQuery
    {
        return Access::admissions('a')
            ->leftJoin(['d' => Discharge::tableName()], 'd.admission_id = a.id')
            ->andWhere(['a.status' => 'admitted', 'd.id' => null])
            ->andWhere(['not', ['a.ready_at' => null]])
            ->orderBy(['a.ready_at' => SORT_ASC, 'a.id' => SORT_ASC]);
    }

    public static function load(): array
    {
        $query = self::query()->with('patient');

        return [
            'readyTotal' => (int) (clone $query)->count(),
            'readyAdmissions' => $query->limit(10)->all(),
        ];
    }
}
