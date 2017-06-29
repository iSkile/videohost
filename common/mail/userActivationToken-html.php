<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify', 'token' => $user->getSecretKey()]);
?>
<div class="email-verification">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Click the link below to activate your account:</p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>
