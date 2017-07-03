<?php

namespace common\helpers;

use common\models\Video;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use xutl\videojs\VideoJsWidget;

class myHelpers
{
    public static function imgPreview($src, $url = '')
    {
        if ($url === false) {
            return Html::tag(
                'div',
                Html::img(
                    $src,
                    ['alt' => 'yii']
                ),
                ['class' => 'img-preview']
            );
        } elseif ($url == '') {
            $url = $src;
        }

        return Html::a(
            Html::img(
                $src,
                ['alt' => 'yii']
            ),
            $url,
            ['class' => 'img-preview']
        );
    }

    public static function videoPlayer($path, $poster = '')
    {
        try {
            $dir = Video::getVideoParentFolderPath();
            return VideoJsWidget::widget([
                'options' => [
                    'class' => 'video-js vjs-big-play-centered',
                    'poster' => $poster,
                    'controls' => true,
                    'preload' => 'none',
                    'data-setup' => '{}',
                ],
                'tags' => [
                    'source' => [
                        ['src' => '/video/' . $path, 'type' => FileHelper::getMimeType($dir . '/' . $path)],
                    ],
                ]
            ]);
        } catch (\Exception $exception) {
            return 'no video';
        }
    }
}