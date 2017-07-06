<?php

namespace frontend\controllers;

use Yii;
use common\models\Topic;
use common\models\Section;
use common\models\Video;
use common\models\Like;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

use yii\data\Pagination;

class PageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['like', 'fave'],
                'rules' => [
                    [
                        'actions' => ['like', 'fave'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'like' => ['put'],
                ],
            ],
        ];
    }

    public function actionSection($section, $topic = '')
    {
        if ($topic) {
            // $this->redirect(['/page/topic', 'section' => $section, 'topic' => $topic]);
            return $this->actionTopic($topic);
        }

        $model = Section::findBySlug(['slug' => $section]);

        if (!$model) {
            throw new NotFoundHttpException('The requested section does not exist.');
        }

        return $this->render('section', [
            'model' => $model,
            'topics' => $model->topics
        ]);
    }

    public function actionTopic($topic)
    {
        $model = Topic::findBySlug(['slug' => $topic]);

        if (!$model) {
            throw new NotFoundHttpException('The requested topic does not exist.');
        }

        $query = $model->getVideos();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $videos = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();


        return $this->render('topic', [
            'model' => $model,
            'videos' => $videos,
            'pages' => $pages,
        ]);
    }

    public function actionFave()
    {
        $query = Like::find()->where(['user_id' => Yii::$app->user->id]);
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('fave', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionLike($video_id)
    {
        $model = Video::findOne(['id' => $video_id]);
        $user = Yii::$app->user;
        $resp = [];

        if (!$model) {
            throw new NotFoundHttpException('The requested video does not exist.');
        }

        if (!$user->identity->hasAccessFor($model->topic->section)) {
            throw new ForbiddenHttpException('You do not have access to this video.');
        }


        if ($model->hasLiked()) {
            $model->dislike();
            $resp['liked'] = false;
        } else {
            $model->like();
            $resp['liked'] = true;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $resp;
    }
}
