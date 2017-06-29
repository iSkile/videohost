<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\rbac\UserRoleRule;

//use common\rbac\VideoRule;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); //удаляем старые данные

        // Включаем наш обработчик
        $rule = new UserRoleRule();
        $auth->add($rule);

//        $videoRule = new VideoRule();
//        $auth->add($videoRule);

        // Добавляем роли
        $admin = $auth->createRole('admin');
        $admin->ruleName = $rule->name;
        $auth->add($admin);

        $user = $auth->createRole('user');
        $user->ruleName = $rule->name;
        $auth->add($user);

        // Создадим права для доступа
        $dashboard = $auth->createPermission('dashboard');
        $dashboard->description = 'Admin panel';
        $auth->add($dashboard);

//        $video = $auth->createPermission('video');
//        $video->description = 'Has user access to video section';
//        $user->ruleName = $videoRule;
//        $auth->add($video);

        // Даём доступ
//        $auth->addChild($user, $video);
        $auth->addChild($admin, $dashboard);

        // Наследование прав
        $auth->addChild($admin, $user);

        // Назначаем роль admin пользователю с ID 1
        // $auth->assign($admin, 1);
    }
}