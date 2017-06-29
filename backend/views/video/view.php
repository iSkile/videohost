<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\MyHelpers;

/* @var $this yii\web\View */
/* @var $model common\models\Video */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Videos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="video-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'path',
                'label' => 'video',
                'format' => 'raw',
                'value' => MyHelpers::videoPlayer($model->path, $model->image->url),
            ],
            'description',
            [
                'attribute' => 'topic_id',
                'label' => 'Topic',
                'format' => 'html',
                'value' => Html::a(
                    $model->topic->name,
                    '/backend/topic/view?id=' . $model->topic->id
                ),
            ],
            [
                'attribute' => 'image_id',
                'label' => 'image',
                'format' => 'html',
                'value' => MyHelpers::imgPreview($model->image->ThumbnailUrl, $model->image->url),
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => $model->getDate($model->updated_at)
            ],
            [
                'attribute' => 'created_by',
                'format' => 'raw',
                'value' => $model->getCreatedBy('username')
            ],

            [
                'attribute' => 'updated_by',
                'format' => 'raw',
                'value' => $model->getCreatedBy('username')
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => $model->getDate($model->updated_at)
            ],
        ],
    ]) ?>

</div>
