<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'email')->textInput() ?>

    <?= $form->field($model, 'section')->widget(Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\common\models\Section::getActiveSectionArray(), 'id', 'name'),
        'options' => [
            'placeholder' => 'Select Sections...',
            'multiple' => true,
        ],
        'pluginOptions' => [
            'tags' => true,
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'role')->dropDownList([
        \common\models\User::ROLE_USER => 'User',
        \common\models\User::ROLE_ADMIN => 'Admin',
    ]);
    ?>
    <?= $form->field($model, 'status')->dropDownList([
        \common\models\User::STATUS_ACTIVE => 'Active',
        \common\models\User::STATUS_WAIT_ACTIVE => 'Wait Activation',
        \common\models\User::STATUS_DELETED => 'Deleted',
    ]);
    ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
