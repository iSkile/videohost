<?php

use common\helpers\myHelpers;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$dir = common\models\Video::getVideoParentFolderPath();
$user = Yii::$app->user;
$access = false;

if (!$user->isGuest && $user->identity->hasAccessFor($model->section)) {
    $access = true;
}

/* @var $this yii\web\View */

/** @var \common\models\Topic $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => $model->section->name, 'url' => ['/page/section', 'section' => $model->section->slug]];
$this->params['breadcrumbs'][] = $this->title;
?>

<? /** @var array $videos */
if (count($videos) == 0) { ?>
    <h1 class="vertical-center">
        <i class="glyphicon glyphicon-info-sign"></i> It's empty, but it's temporary
    </h1>
<? } else { ?>
    <div class="row">
        <? foreach ($videos as $video) { ?>
            <div class="col-lg-3 col-sm-6">
                <? if ($access) { ?>
                    <div class="box"
                         data-src="<?= Html::encode($video->url) ?>"
                         data-type="<?= FileHelper::getMimeType($dir . '/' . $video->path) ?>"
                         data-fave="<?= (int)$video->hasLiked() ?>"
                         data-fave-url="<?= Url::to(['/page/like',
                             'section' => $model->section->slug,
                             'topic' => $model->slug,
                             'video_id' => $video->id
                         ]) ?>">

                        <?= myHelpers::imgPreview(
                            $video->image->getThumbnailUrl(), false
                        ) ?>

                        <div class="info">
                            <div class="name"><?= Html::encode($video->name) ?></div>
                            <div class="description"><?= Html::encode($video->description) ?></div>
                        </div>
                    </div>
                <? } else { ?>
                    <div class="box">
                        <?= myHelpers::imgPreview(
                            $video->image->getThumbnailUrl(), false
                        ) ?>
                        <div class="info">
                            <div class="name"><?= Html::encode($video->name) ?></div>
                            <div class="description"><?= Html::encode($video->description) ?></div>
                        </div>
                    </div>
                <? } ?>
            </div>
        <? } ?>
    </div>

    <?= /** @var \yii\data\Pagination $pages */
    LinkPager::widget([
        'pagination' => $pages,
    ]); ?>


    <? if ($access) { ?>
        <!-- Video Modal -->
        <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="videoModalLabel"></h4>
                    </div>
                    <div id="modal-video">

                    </div>
                    <div class="modal-body clearfix">
                        <button id="fave" data-checked="false" type="button" class="btn btn-primary pull-right">
                            <i class="glyphicon glyphicon-star"></i> Fave
                        </button>
                        <p id="description"></p>
                    </div>
                </div>
            </div>
        </div>
        <?
        $this->registerJsFile(
            Yii::$app->request->baseUrl . '/js/video.js',
            ['depends' => [\yii\web\JqueryAsset::className()]]
        );
        $this->registerAssetBundle(\xutl\videojs\VideoJsAsset::className());
        ?>
    <? } else { ?>
        <? // guest or no access
        $this->registerJs(
            'var errorModal = $("#errorModal");
                errorModal.on("hide.bs.modal", function (e) {
                    location.href="' . Url::to(['/site/index']) . '";
                });
                errorModal.find(".modal-body").html("<div class=\'error\'>You do not have access to videos in this section.</div>");
                $(".box").on("click", function (e) {e.preventDefault();errorModal.modal("show");});'
        ); ?>
    <? } // end guest ?>
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalLabel">Error</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>
<? } // if count videos ?>