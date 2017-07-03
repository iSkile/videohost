<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\UploadedFile;

//use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "section".
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property integer $status
 * @property integer $image_id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Image $image
 * @property Subscription[] $subscriptions
 * @property User[] $users
 * @property Topic[] $topics
 */
class Section extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INV = 5;
    const STATUS_DELETED = 0;

    public $imageFile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'section';
    }

    public static function getActiveSectionArray()
    {
        return self::findAll(['status' => self::STATUS_ACTIVE]);
    }

    public static function findBySlug($slug)
    {
        return static::findOne([
            'slug' => $slug,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['status', 'image_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name', 'slug'], 'unique'],
            [['slug'], 'checkBlacklist'],
            [['slug'], 'match', 'pattern' => '/^[a-z\d\-\_]+$/i', 'message' => 'Only letters, numbers, hyphens, and underscores'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'on' => 'create'],
            'imageFile' => Image::getRule(),
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INV,
                self::STATUS_DELETED,
            ]],
        ];
    }

    public function checkBlacklist()
    {
        $blacklist = [
            'backend', 'fave', 'site', 'video', 'image', 'verify',// url
            'assets', 'css', 'fonts', 'images' // folders
        ];

        if (in_array($this->slug, $blacklist)) { // strtolower()
            $this->addError('slug', 'This field can not be called: ' . implode(', ', $blacklist));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'status' => 'Status',
            'image_id' => 'Image ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::className(), ['id' => 'image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::className(), ['section_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('subscription', ['section_id' => 'id']);
    }

    // ----------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['section_id' => 'id']);
    }

    public function getCreatedBy($attribute)
    {
        /** @var User $user */
        $user = User::findOne($this->created_by);

        return $user->hasAttribute($attribute) ? $user->{$attribute} : $user->email;
    }

    public function getUpdatedBy($attribute)
    {
        /** @var User $user */
        $user = User::findOne($this->updated_by);

        return $user->hasAttribute($attribute) ? $user->{$attribute} : $user->email;
    }

    public function getDate($date)
    {
        return Yii::$app->formatter->asDate($date, 'medium');
    }

    public function getTopicsHTML()
    {
        $topics = $this->topics;
        $links = [];
        foreach ($topics as $link) {
            $links[] = Html::a(
                Html::encode($link->name),
                Yii::$app->request->hostInfo . '/backend/topic/' . $link->id
            );
        }
        return implode(', ', $links);
    }

    public function statusName()
    {
        $name = '';
        switch ($this->status) {
            case self::STATUS_DELETED:
                $name = 'Deleted';
                break;
            case self::STATUS_INV:
                $name = 'Invisible';
                break;
            case self::STATUS_ACTIVE:
                $name = 'Active';
                break;
        }

        return $name;
    }

    /**
     * @return bool
     */
    public function uploadImage()
    {
        $this->imageFile = UploadedFile::getInstance($this, 'imageFile');
        if ($this->validate(['imageFile'])) {
            /** Пользуемся своей функцией для аплоада картинки, получаем обьект картинки */
            if ($image = Image::upload($this, 'sections', $this->image ? $this->image->id : null)) {
                $this->image_id = $image->id;
                $this->scenario = 'default';
                return true;
            }
        }

        return !$this->hasErrors();
        // return $this->scenario != 'create';
    }

    public function beforeSave($insert)
    {
        if ($this->status != $this::STATUS_ACTIVE) {
            // safe delete topics
            $topics = $this->topics;
            foreach ($topics as $topic) {
                $topic->hide();
            }
        }

        return parent::beforeSave($insert);
    }

    /** Safe delete */
    public function delete()
    {
        // delete subscription to this section
        foreach ($this->users as $user) {
            $user->removeSection($this);
        }

        /**
         * Section delete
         */
        $this->status = self::STATUS_DELETED;

        //$this->name = $this->name . $this->id;
        //$this->slug = $this->slug . $this->id;

        $this->image->clearThumbnails();

        return $this->save();
    }
}
