<?php
namespace app\components;

use yii\filters\AccessControl;
use yii\web\Controller;

class RoleController extends Controller
{
    public function behaviors()
    {
        return ['access' => [
            'class' => AccessControl::class,
            'rules' => [['allow' => true, 'matchCallback' => fn($rule, $action) => Access::allows($action->getUniqueId())]],
        ]];
    }
}
