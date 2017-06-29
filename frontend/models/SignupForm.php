<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $userExists = (bool)User::find()->one();

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        if ($userExists) {
            $user->generateSecretKey();

            Yii::$app
                ->mailer
                ->compose(
                    ['html' => 'userActivationToken-html', 'text' => 'userActivationToken-text'],
                    ['user' => $user]
                )
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                ->setTo($this->email)
                ->setSubject(Yii::$app->name . '. Account activation for ' . Html::encode($user->username))
                ->send();

            Yii::$app->session->setFlash('info', 'Check your email for further instructions.');
        } else {
            /** если в базе нет пользователей - присваиваем роль админа */
            $user->role = User::ROLE_ADMIN;
        }

        return $user->save() ? $user : null;
    }
}
