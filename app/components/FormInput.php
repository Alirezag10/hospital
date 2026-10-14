<?php

namespace app\components;

use yii\base\Model;
use yii\web\BadRequestHttpException;

class FormInput
{
    public static function load(Model $model, array $data): bool
    {
        $name = $model->formName();
        if (!array_key_exists($name, $data)) {
            return false;
        }
        $values = $data[$name];
        if (!is_array($values)) {
            throw new BadRequestHttpException('ساختار اطلاعات فرم معتبر نیست.');
        }
        // These forms accept scalar fields only; unsafe fields remain ignored by load().
        foreach ($model->safeAttributes() as $attribute) {
            if (isset($values[$attribute]) && !is_scalar($values[$attribute])) {
                throw new BadRequestHttpException('ساختار اطلاعات فرم معتبر نیست.');
            }
        }
        return $model->load($data);
    }
}
