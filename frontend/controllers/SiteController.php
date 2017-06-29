<?php
namespace frontend\controllers;

//use common\helpers\myHelpers;
use common\models\Section;
use common\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;

//use frontend\models\ContactForm;
//use yii\helpers\FileHelper;
use common\models\Video;
use common\models\Image;
use yii\imagine\Image as Imagine;


/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'video'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'sections' => Section::getActiveSectionArray(),
        ]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            /** ToDo
             * add user no active error
             */
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Generate and show image thumbnails
     *
     * @param $id
     * @param int $width
     * @param int $height
     * @throws NotFoundHttpException
     */
    public function actionImage($id, $width = 0, $height = 0)
    {
        $image = Image::findOne($id);

        if (!$image) {
            throw new NotFoundHttpException('The requested image does not exist.');
        }

        $args = func_get_args();
        $width = 300;
        $height = 200;

        if ($args[1]) {
            $width = $args[1];
            if (!$args[2]) {
                $height = $width;
            } else {
                $height = $args[2] ? $args[2] : 200;
            }
        }

        $dir = Image::getImageParentFolderPath();
        $path = $dir . '/' . $image->path;
        $thumb = Yii::getAlias('@storage/thumbnails/' . sha1($image->path . $width . $height) . '.jpg');

        if (!file_exists($thumb)) {
            Imagine::thumbnail($path, $width, $height)->save($thumb, ['quality' => 90]);
            // Imagine::getImagine()->open($path)->thumbnail(new Box($width, $height))->save($thumb, ['quality' => 90]);
        }

        /** ToDo
         * change to xSendFile()
         * http://www.yiiframework.com/doc-2.0/yii-web-response.html#xSendFile()-detail
         */
        Yii::$app->response->sendFile($thumb)->send();
    }

    /**
     * Gives video with verification of access rights
     *
     * @param $name
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionVideo($name)
    {
        $user = Yii::$app->user;
        $video = Video::findOne(['path' => $name]);
        $dir = Video::getVideoParentFolderPath();

        if (!$video) {
            throw new NotFoundHttpException('The requested video does not exist.');
        }

        if (!$user->identity->hasAccessFor($video->topic->section)) {
            throw new ForbiddenHttpException('You do not have access to this video.');
        }

        /** ToDo
         * change to xSendFile()
         * http://www.yiiframework.com/doc-2.0/yii-web-response.html#xSendFile()-detail
         */
        Yii::$app->response->sendFile($dir . '/' . $name)->send();
    }

    /**
     * Confirm email
     *
     * @param $token
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionVerify($token)
    {
        $user = User::findBySecretKey($token);

        if (!$user) {
            throw new NotFoundHttpException('Wrong verification token.');
        }

        $user->removeSecretKey();
        $user->status = User::STATUS_ACTIVE;

        if ($user->save()) {
            Yii::$app->session->setFlash('success', 'Account activated.');
        } else {
            Yii::$app->session->setFlash('error', 'Account not activated.');
        }

        return $this->goHome();
    }
}
