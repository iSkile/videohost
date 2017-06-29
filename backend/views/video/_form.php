<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\helpers\MyHelpers;
use kartik\select2\Select2;
//use common\models\Section;
use common\models\Topic;


/* @var $this yii\web\View */
/* @var $model common\models\Video */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="video-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <!--    --><? //= $form->field($model, 'path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

    <!--    --><? //= $form->field($model, $model->topic->section->id)->dropDownList(
    //        ArrayHelper::map(Topic::getActiveTopicsArray(), 'id', 'name'),
    //        ['prompt' => 'Select Section',
    //            'onchange' => '
    //                $.post( "index.php?r=test/branches/lists&id="+$(this).val(), function( data ) {
    //                  $( "select#departments-branches_branch_id" ).html( data );
    //                });'
    //        ]);
    //    ?>

    <?= $form->field($model, 'topic_id')->dropDownList(
        ArrayHelper::map(Topic::getActiveTopicsArray(), 'id', 'name'),
        ['prompt' => 'Select Topic']
    )->label('Topic') ?>

    <?= $form->field($model, 'imageFile', [
        'template' => "{label}\n" . ($model->image ? MyHelpers::imgPreview($model->image->ThumbnailUrl, $model->image->url) . '<br>' : '') . "{input}\n{hint}\n{error}"
    ])->fileInput(['accept' => 'image/*'])->label('Image') ?>


    <?= $form->field($model, 'videoFile', [
        'template' => "{label}\n" . ($model->path ? MyHelpers::videoPlayer($model->path) . '<br>' : '') . "{input}\n{hint}\n{error}"
    ])->fileInput(['accept' => 'video/*'])->label('Video') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
