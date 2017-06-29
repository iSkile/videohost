<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\helpers\MyHelpers;

/* @var $this yii\web\View */
/* @var $model common\models\Section */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="section-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList([
        \common\models\Section::STATUS_ACTIVE => 'Active',
        \common\models\Section::STATUS_INV => 'Invisible',
        \common\models\Section::STATUS_DELETED => 'Deleted',
    ])
    ?>

    <?= $form->field($model, 'imageFile', [
        'template' => "{label}\n" . ($model->image ? MyHelpers::imgPreview($model->image->ThumbnailUrl, $model->image->url) . '<br>' : '') . "{input}\n{hint}\n{error}"
    ])->fileInput(['accept' => 'image/*'])->label('Image') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
