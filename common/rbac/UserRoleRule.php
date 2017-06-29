<?php

namespace common\rbac;

use Yii;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class UserRoleRule extends Rule
{
    public $name = 'userRole';

    public function execute($user, $item, $params)
    {
        //Получаем массив пользователя из базы
        $user = ArrayHelper::getValue($params, 'user', Yii::$app->user->identity);
        // Yii::$app->user->id == $user ? ... : User::findOne($user)

        if ($user) {
            $role = $user->role;

            if ($item->name === 'admin') {
                return $role == User::ROLE_ADMIN;
            } elseif ($item->name === 'user') {
                return $role == User::ROLE_ADMIN || $role == User::ROLE_USER;
            }
        }
        return false;
    }
}