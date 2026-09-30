<?php

namespace app\components;

use Yii;
use yii\filters\AccessRule as BaseAccessRule;

class AccessRule extends BaseAccessRule
{
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role === '@' && !$user->isGuest) {
                return true;
            }

            if (
                $role === 'admin'
                && Yii::$app->user->identity?->role === 'admin'
            ) {
                return true;
            }
        }

        return false;
    }
}