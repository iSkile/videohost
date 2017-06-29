<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "topic".
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property integer $status
 * @property integer $section_id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Section $section
 * @property Video[] $videos
 */
class Topic extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_INV = 5;
    const STATUS_DELETED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'topic';
    }

    public static function findBySlug($slug)
    {
        return static::findOne([
            'slug' => $slug,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slug', 'section_id'], 'required'],
            [['status', 'section_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name', 'slug'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-z\d\-\_]+$/i', 'message' => 'Only letters, numbers, hyphens, and underscores'],
            [['section_id'], 'exist', 'skipOnError' => true, 'targetClass' => Section::className(), 'targetAttribute' => ['section_id' => 'id']],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INV,
                self::STATUS_DELETED,
            ]],
            [['status'], 'validateStatus'],
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
            'slug' => 'Slug',
            'status' => 'Status',
            'section_id' => 'Section ID',
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
     * validate status, don't show topic in hidden section
     */
    public function validateStatus()
    {
        if (($this->status === self::STATUS_ACTIVE) && ($this->section->status !== Section::STATUS_ACTIVE)) {
            $this->addError('status', 'You can not show since the section in which this topic is located is not available.');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSection()
    {
        return $this->hasOne(Section::className(), ['id' => 'section_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideos()
    {
        return $this->hasMany(Video::className(), ['topic_id' => 'id']);
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

    public function getVideosHTML()
    {
        $topics = $this->videos;
        $links = [];
        foreach ($topics as $link) {
            $links[] = Html::a(
                Html::encode($link->name),
                Yii::$app->request->hostInfo . '/backend/video/view?id=' . $link->id
            );
        }
        return implode(', ', $links);
    }

    public static function getActiveTopicsArray()
    {
        return self::findAll([
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public function hide()
    {
        $this->status = self::STATUS_INV;
        return $this->save();
    }

    /** Safe delete */
    public function delete()
    {
        $this->status = self::STATUS_DELETED;

        // safe delete video
        $videos = $this->videos;
        foreach ($videos as $video) {
            $video->delete();
        }


        return $this->save();
    }
}
