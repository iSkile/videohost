<?php

use common\helpers\myHelpers;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$dir = common\models\Video::getVideoParentFolderPath();
$user = Yii::$app->user;

/* @var $this yii\web\View */
/** @var \common\models\Like $models */

$this->title = 'Favorite video';
$this->params['breadcrumbs'][] = $this->title;
?>

<? if (count($models) == 0) { ?>
    <h1 class="vertical-center">
        <i class="glyphicon glyphicon-info-sign"></i> It's empty, but it's temporary
    </h1>
<? } else { ?>
    <div class="row">
        <? foreach ($models as $model) { ?>
            <div class="col-lg-3 col-sm-6">
                <div class="box"
                     data-src="<?= Html::encode($model->video->url) ?>"
                     data-type="<?= FileHelper::getMimeType($dir . '/' . $model->video->path) ?>"
                     data-fave="<?= (int)$model->video->hasLiked() ?>"
                     data-fave-url="<?= Url::to(['/page/like',
                         'section' => $model->video->topic->section->slug,
                         'topic' => $model->video->topic->slug,
                         'video_id' => $model->video->id
                     ]) ?>">

                    <?= myHelpers::imgPreview(
                        $model->video->image->getThumbnailUrl(), false
                    ) ?>

                    <div class="info">
                        <div class="name"><?= Html::encode($model->video->name) ?></div>
                        <div class="description"><?= Html::encode($model->video->description) ?></div>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>

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
                        <i class="glyphicon glyphicon-star-empty"></i> Fave
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