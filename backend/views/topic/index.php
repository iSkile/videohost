<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TopicSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Topics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="topic-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Topic', ['create'], ['class' => 'btn btn-success']) ?>
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
            [
                'attribute' => 'slug',
                'format' => 'html',
                'content' => function ($data) {
                    return Html::a(
                        $data->slug,
                        Yii::$app->request->hostInfo . "/{$data->section->slug}/{$data->slug}"
                    );
                },
            ],
            [
                'label' => 'Videos',
                'format' => 'html',
                'content' => function ($data) {
                    return $data->videoshtml;
                },
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'content' => function ($data) {
                    return $data->statusName();
                },
            ],
            [
                'attribute' => 'section_id',
                'label' => 'Section',
                'format' => 'html',
                'content' => function ($data) {
                    return Html::a(
                        Html::encode($data->section->name),
                        '/backend/section/' . $data->section->id
                    );
                },
            ],
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
