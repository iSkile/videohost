<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify', 'token' => $user->getSecretKey()]);
?>
Hello <?= $user->username ?>,

Click the link below to activate your account:

<?= $verifyLink ?>
