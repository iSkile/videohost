<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Section $model */
/** @var \common\models\Topic $topics */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;

foreach ($topics as $topic) { ?>
    <div class="topic">
        <h2><?= $topic->name ?></h2>
        <!--        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>-->
        <?= Html::a(
            'View &raquo;',
            Url::to(['/page/topic', 'section' => $model->slug, 'topic' => $topic->slug]),
            ['class' => 'btn btn-default']
        ) ?>
    </div>
<? } ?>

<? if (count($topics) == 0) { ?>
    <h1 class="vertical-center">
        <i class="glyphicon glyphicon-info-sign"></i> It's empty, but it's temporary
    </h1>
<?php } ?>
