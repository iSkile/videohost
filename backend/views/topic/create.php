<?php

use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model common\models\Topic */

$this->title = 'Create Topic';
$this->params['breadcrumbs'][] = ['label' => 'Topics', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="topic-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <?php
    $this->registerJsFile(
        Yii::$app->request->baseUrl . '/js/slug.js',
        ['depends' => [\yii\web\JqueryAsset::className()]]
    );
    ?>
</div>
