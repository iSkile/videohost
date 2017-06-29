<?php

use common\helpers\myHelpers;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/** @var \common\models\Section $sections */

$this->title = 'Sections';
/*
?>
<div class="row">
    <?php
    foreach ($sections as $section) {
        ?>
        <div class="col-lg-3 col-sm-6">
            <div class="box">
                <?= myHelpers::imgPreview(
                    $section->image->getThumbnailUrl(),
                    Url::to(['/page/section', 'section' => $section->slug])
                ) ?>
                <div class="info">
                    <div class="name"><?= Html::encode($section->name) ?></div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
*/
?>

<div class="row">
    <?php
    foreach ($sections as $section) {
        ?>
        <div class="col-lg-3 col-sm-6">
            <div class="thumbnail">
                <?= myHelpers::imgPreview(
                    $section->image->getThumbnailUrl(),
                    Url::to(['/page/section', 'section' => $section->slug])
                ) ?>
                <div class="caption">
                    <h4 class="name"><?= Html::encode($section->name) ?></h4>
                    <p>
                        <a href="<?= Url::to(['/page/section', 'section' => $section->slug]) ?>" class="btn btn-primary"
                           role="button">View >></a>
                    </p>
                </div>
            </div>
        </div>
    <?php } ?>
</div>