<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "video".
 *
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property string $description
 * @property integer $topic_id
 * @property integer $image_id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Like[] $likes
 * @property User[] $users
 * @property Image $image
 * @property Topic $topic
 */
class Video extends \yii\db\ActiveRecord
{
    public $imageFile;
    public $videoFile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'video';
    }

    public function getURL()
    {
        return Yii::$app->request->hostInfo . '/video/' . $this->path;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'path', 'description', 'topic_id', 'image_id'], 'required'],
            [['topic_id', 'image_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'path', 'description'], 'string', 'max' => 255],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['topic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Topic::className(), 'targetAttribute' => ['topic_id' => 'id']],
            [['imageFile', 'videoFile'], 'file', 'skipOnEmpty' => false, 'on' => 'create'],
            'imageFile' => Image::getRule(),
            [['imageFile'], 'image', 'maxSize' => 1024 * 512, 'maxWidth' => 540, 'maxHeight' => 360],
            [['videoFile'], 'file', 'extensions' => ['mp4', 'webm', 'ogg']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'path' => 'Path',
            'description' => 'Description',
            'topic_id' => 'Topic ID',
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
    public function getLikes()
    {
        return $this->hasMany(Like::className(), ['video_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('like', ['video_id' => 'id']);
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
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

    public function hasLiked()
    {
        return (bool)Like::findOne([
            'user_id' => Yii::$app->user->id,
            'video_id' => $this->id
        ]);
    }

    public function like()
    {
        if ($this->hasLiked()) return true;

        $like = new Like();
        $like->load(['Like' => [
            'user_id' => Yii::$app->user->id,
            'video_id' => $this->id
        ]]);

        return $like->save();
    }

    public function dislike()
    {
        if (!$this->hasLiked()) return true;

        $like = Like::findOne([
            'user_id' => Yii::$app->user->id,
            'video_id' => $this->id
        ]);

        if ($like) {
            $like->delete();
        }

        return true;
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

    /**
     * @return bool
     */
    public function uploadImage()
    {
        $this->imageFile = UploadedFile::getInstance($this, 'imageFile');
        if ($this->validate(['imageFile'])) {
            /** Пользуемся своей функцией для аплоада картинки, получаем обьект картинки */
            if ($image = Image::upload($this, 'videos', $this->image ? $this->image->id : null)) {
                $this->image_id = $image->id;
                return true;
            }
        }

        return !$this->hasErrors();
        // return $this->scenario != 'create';
    }

    /**
     * @return bool
     */
    public function uploadVideo()
    {
        $this->videoFile = UploadedFile::getInstance($this, 'videoFile');
        if ($this->validate(['videoFile'])) { // validate 1 attribute
            if (!$this->videoFile) {
                return true;
            }
            $videoName = time() . uniqid() . '.' . $this->videoFile->extension;

            $directory = self::getVideoParentFolderPath();
            $path = "$directory/$videoName";

            if (!$this->isNewRecord) {
                try {
                    unlink($directory . '/' . $this->path);
                } catch (\Exception $exception) {
                    //log
                }
            }

            $this->videoFile->saveAs($path);
            $this->path = $videoName;

            $this->videoFile = false;
            $this->scenario = 'default'; // fix, после сохранения файла он не проходит валидацию, так как временный файл удаляется

            return true;
        }

        return false;
    }

    /**
     * @return bool|string videos folder
     */
    public static function getVideoParentFolderPath()
    {
        return Yii::getAlias('@storage/videos');
    }

    public function beforeDelete()
    {
        foreach ($this->likes as $like) {
            $like->delete();
        }
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        $path = self::getVideoParentFolderPath() . '/' . $this->path;
        try {
            unlink($path);
        } catch (\Exception $exception) {
            //log
        }

        $this->image->delete();

        return parent::afterDelete();
    }
}
