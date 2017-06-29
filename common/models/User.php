<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $role
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_WAIT_ACTIVE = 5;
    const STATUS_ACTIVE = 10;

    const ROLE_ADMIN = 1;
    const ROLE_USER = 0;

    public $password;
    public $section;

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email secret key
     *
     * @param string $secret_key email secret key
     * @return static|null
     */
    public static function findBySecretKey($secret_key)
    {
        return static::findOne([
            'secret_key' => $secret_key,
            'status' => self::STATUS_WAIT_ACTIVE,
        ]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

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

            ['auth_key', 'default', 'value' => null],
            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => [
                self::ROLE_USER,
                self::ROLE_ADMIN,
            ]],
            ['status', 'default', 'value' => self::STATUS_WAIT_ACTIVE],
            ['status', 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_WAIT_ACTIVE,
                self::STATUS_DELETED,
            ]],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }


    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
        if ($this->validate(['password'])) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates "Email confirm code" authentication key
     */
    public function generateSecretKey()
    {
        $this->secret_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Removes "Email confirm code" token
     */
    public function removeSecretKey()
    {
        $this->secret_key = null;
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function statusName()
    {
        $name = '';
        switch ($this->status) {
            case self::STATUS_DELETED:
                $name = 'Deleted';
                break;
            case self::STATUS_WAIT_ACTIVE:
                $name = 'Wait activation';
                break;
            case self::STATUS_ACTIVE:
                $name = 'Active';
                break;
        }

        return $name;
    }

    public function roleName()
    {
        $name = '';
        switch ($this->role) {
            case self::ROLE_USER:
                $name = 'User';
                break;
            case self::ROLE_ADMIN:
                $name = 'Admin';
                break;
        }

        return $name;
    }

    public function getAvailableSections()
    {
        return Section::find()
            ->joinWith('users')
            ->where([User::tableName() . '.id' => $this->id])
            ->all();
    }

    // Section helpers

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function getSectionsHTML()
    {
        $sections = $this->AvailableSections;
        $links = [];
        foreach ($sections as $link) {
            $links[] = Html::a(
                Html::encode($link->name),
                Yii::$app->request->hostInfo . '/backend/section/' . $link->id
            );
        }
        return implode(', ', $links);
    }

    public function hasAccessFor(Section $section)
    {
        if ($this->role == self::ROLE_ADMIN) { // admin is VIP user)
            return true;
        }

        $subscription = Subscription::findOne([
            'user_id' => $this->id,
            'section_id' => $section->id
        ]);

        return $subscription ? true : false;
    }

    public function addSection(Section $section)
    {
        $subscriptionExists = Subscription::findOne([
            'user_id' => $this->id,
            'section_id' => $section->id
        ]);

        if (!$subscriptionExists) {
            $subscription = new Subscription();

            $subscription->load(['Subscription' => [
                'user_id' => $this->id,
                'section_id' => $section->id
            ]]);

            return $subscription->save();
        }

        return true;
    }

    public function setSections($sections_id)
    {
        $availableSections = $this->AvailableSections;
        $availableSectionsID = [];

        foreach ($availableSections as $section) {
            $availableSectionsID[] = $section->id;
        }

        // Удаление подписок на секции не переданные в массиве
        foreach ($availableSections as $section) {
            if (!in_array($section->id, $sections_id)) {
                $this->removeSection($section);
            }
        }

        // Добавление новых подписок
        foreach ($sections_id as $section_id) {
            // Проверка, подписан ли пользователь на секцию
            if (!in_array($section_id, $availableSectionsID)) {
                $section = Section::findOne($section_id);

                /** если секцию нашло - добавляем ее к пользовател, если нет - пропускаем шаг */
                if ($section) {
                    $this->addSection($section);
                }
            }
        }

        return true;
    }

    public function removeSection(Section $section)
    {
        $subscription = Subscription::findOne([
            'user_id' => $this->id,
            'section_id' => $section->id
        ]);

        if ($subscription) {
            $subscription->delete();
        }

        return true;
    }

    public function getDate($date)
    {
        return Yii::$app->formatter->asDate($date, 'medium');
    }


    /** Safe delete */
    public function delete()
    {
        $this->status = self::STATUS_DELETED;

        return $this->save();
    }
}
