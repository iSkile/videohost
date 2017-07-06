<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Dashboard';

$indexLinks = [
    ['label' => 'Users', 'url' => ['/user/index']],
    ['label' => 'Sections', 'url' => ['/section/index']],
    ['label' => 'Topics', 'url' => ['/topic/index']],
    ['label' => 'Videos', 'url' => ['/video/index']],
];
?>
<div class="site-index">
    <div class="list-group">
        <? foreach ($indexLinks as $link) {
            echo Html::a($link['label'], $link['url'], ['class' => 'list-group-item']);
        } ?>
    </div>
</div>
