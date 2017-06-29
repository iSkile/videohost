<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\helpers\MyHelpers;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\VideoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Videos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="video-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Video', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            [
                'attribute' => 'name',
                'format' => 'html',
                'content' => function ($data) {
                    return Html::a(Html::encode($data->name), $data->id);
                },
            ],
            'description',
            [
                'attribute' => 'topic_id',
                'label' => 'Topic',
                'format' => 'html',
                'content' => function ($data) {
                    return Html::a(
                        Html::encode($data->topic->name),
                        '/backend/topic/view?id=' . $data->topic->id
                    );
                },
            ],
            [
                'attribute' => 'path',
                'label' => 'video',
                'format' => 'raw',
                'content' => function ($data) {
                    return MyHelpers::videoPlayer($data->path, $data->image->url);
                },
            ],
            // [
            //     'attribute' => 'image_id',
            //     'label' => 'Image',
            //     'format' => 'html',
            //     'content' => function($data) { return MyHelpers::imgPreview($data->image->ThumbnailUrl, $data->image->url); },
            // ],
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
