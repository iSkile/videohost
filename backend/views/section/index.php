<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\helpers\MyHelpers;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SectionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sections';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="section-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Section', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
//            'id',
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
                        Yii::$app->request->hostInfo . '/' . $data->slug
                    );
                },
            ],
            [
                'label' => 'topics',
                'format' => 'html',
                'content' => function ($data) {
                    return $data->topicshtml;
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
                'attribute' => 'image_id',
                'label' => 'Image',
                'format' => 'html',
                'content' => function ($data) {
                    return MyHelpers::imgPreview($data->image->ThumbnailUrl, $data->image->url);
                },
            ],
            // 'image_id',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
