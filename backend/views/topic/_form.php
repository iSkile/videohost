<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Topic */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="topic-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList([
        \common\models\Topic::STATUS_ACTIVE => 'Active',
        \common\models\Topic::STATUS_INV => 'Invisible',
        \common\models\Topic::STATUS_DELETED => 'Deleted',
    ])
    ?>

    <?= $form->field($model, 'section_id')->dropDownList(
        \yii\helpers\ArrayHelper::map(\common\models\Section::getActiveSectionArray(), 'id', 'name'),
        ['prompt' => 'Select section']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
