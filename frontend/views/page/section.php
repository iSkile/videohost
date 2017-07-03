<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Section $model */
/** @var \common\models\Topic $topics */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;

if (count($topics) == 0) { ?>
    <h1 class="vertical-center">
        <i class="glyphicon glyphicon-info-sign"></i> It's empty, but it's temporary
    </h1>
<? } else { ?>
    <!--    <div class="list-group">-->
    <!--        --><? // foreach ($topics as $topic) { ?>
    <!--            <a href="--><? //= Url::to(['/page/topic', 'section' => $model->slug, 'topic' => $topic->slug]) ?><!--"-->
    <!--               class="list-group-item">-->
    <!--                <h4 class="list-group-item-heading">--><? //= Html::encode($topic->name) ?><!--</h4>-->
    <!--                <p class="list-group-item-text">--><? //= Html::encode($topic->description) ?><!--</p>-->
    <!--            </a>-->
    <!--        --><? // } ?>
    <!--    </div>-->
    <? foreach ($topics as $topic) { ?>
        <div class="topic">
            <h2><?= Html::encode($topic->name) ?></h2>
            <p><?= Html::encode($topic->description) ?></p>
            <?= Html::a(
                'View &raquo;',
                Url::to(['/page/topic', 'section' => $model->slug, 'topic' => $topic->slug]),
                ['class' => 'btn btn-default']
            ) ?>
        </div>
    <? } ?>
<? } ?>
