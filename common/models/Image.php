<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

//use common\helpers\myHelpers;
//use yii\imagine\Image as Imagine;
//use Imagine\Image\Box;
//use yii\helpers\Html;
//use yii\helpers\Url;
//use yii\web\UploadedFile;

/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Section[] $sections
 * @property Video[] $videos
 */
class Image extends \yii\db\ActiveRecord
{
    public $imageFile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }


    /**
     * @return array with image rules
     */
    public static function getRule()
    {
        return ['imageFile', 'image', 'extensions' => 'png, jpg, gif'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'path'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['path'], 'string', 'max' => 255],
            'imageFile' => self::getRule(),
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
    public function getSections()
    {
        return $this->hasMany(Section::className(), ['image_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideos()
    {
        return $this->hasMany(Video::className(), ['image_id' => 'id']);
    }

    public static function getImageParentFolderPath()
    {
        return FileHelper::normalizePath(Yii::getAlias('@frontend/web/images/'));
    }

    public static function getImagesParentFolderLink()
    {
        return Yii::$app->request->hostInfo . '/images/';
    }

    public function getURL()
    {
        return self::getImagesParentFolderLink() . $this->path;
    }

    public function getThumbnailUrl($width = 0, $height = 0)
    {
        if ($height != 0) {
            $params = "/$width/$height";
        } elseif ($width != 0) {
            $params = "/$width";
        } else {
            $params = '';
        }

        return Yii::$app->request->hostInfo . '/image/' . $this->id . $params;
    }

    public function getThumbnailPath($width, $height)
    {
        return Yii::getAlias('@storage/thumbnails/' . sha1($this->path) . ($width . 'x' . $height) . '.jpg');
    }

    /**
     * @param $model Model Сюда мы передадим обьект модели с файлом в поле imageFile
     * @param $folder string Имя папки, в которую мы загрузим файл
     * @param null $id int Если нам нужно обновить картинку, а не загрузить новую, мы указываем ИД картинки в БД. По умолчанию null
     * @return Image|null
     */
    public static function upload($model, $folder, $id = null)
    {
        $file = &$model->imageFile;

        if (!$file) {
            // $model->addError('imageFile', 'no file');
            return null;
        }

        // имя файла, не вжно какое оно будет, главное чтобы не повторялось в переделе директории,
        // поэтому я выбрал  что первой частью имени будет текущий timestamp, а второй - уникальный набор символов.
        $imageName = time() . uniqid() . '.' . $file->extension;

        // берем полный путь папки в которую юудем загружать картинки
        $path = self::getImageParentFolderPath();

        $directory = $path . '/' . $folder;

        if (!$id) {
            $modelImage = new Image();
        } else {
            $modelImage = Image::findOne($id);
        }

        if (!$modelImage->isNewRecord) {
            $modelImage->clearThumbnails();
            $modelImage->deleteImage();
        }

        /** делаем необходинмые манипуляции с обьектом картинки  */
        $modelImage->name = $file->baseName;
        $modelImage->path = $folder . "/$imageName";
        $modelImage->imageFile = $file;

        if ($modelImage->save()) {
            FileHelper::createDirectory($directory, 0644);
            $file->saveAs("$directory/$imageName");
            $model->imageFile = null;
            return $modelImage;
        } else {
            $model->addErrors(
                $modelImage->getErrors()
            );
        }

        return null;
    }

    /**
     * delete all thumbnail images
     */
    public function clearThumbnails()
    {
        $thumbnails = glob($this->getThumbnailPath('*', '*'));

        try {
            foreach ($thumbnails as $thumbnail) {
                unlink($thumbnail);
            }
        } catch (\Exception $exception) {
            //log
        }
    }

    /**
     * delete image
     */
    private function deleteImage()
    {
        $path = self::getImageParentFolderPath() . '/' . $this->path;
        try {
            unlink($path);
        } catch (\Exception $exception) {
            //log
        }
    }

    public function delete()
    {
        $this->clearThumbnails();
        $this->deleteImage();

        return parent::delete();
    }
}
