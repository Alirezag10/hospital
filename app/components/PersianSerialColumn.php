<?php
namespace app\components;

class PersianSerialColumn extends \yii\grid\SerialColumn
{
    protected function renderDataCellContent($model, $key, $index)
    {
        return \Yii::$app->formatter->asInteger(parent::renderDataCellContent($model, $key, $index));
    }
}
