<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $title
 * @property string $anons
 * @property string $text
 * @property string $datatime
 * @property string $image_path
 *
 * @property Comment[] $comments
 * @property PostTag[] $postTags
 * @property Tag[] $tags
 */
class Post extends \yii\db\ActiveRecord
{
    public $image;

    public function fields()
    {
        $fields = [
            'title',
            'datatime' => function($post) {
                $time = strtotime($post->datatime);

                return date('H:i d.m.Y', $time);
            },
            'anons',
            'text',
            'tags' => function($post) {
                return ArrayHelper::getColumn($post->tags, 'name');
            },
            'image' => function($post) {
                return Url::home(true) . $post->image_path;
            },
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'anons', 'text'], 'required'],
            [['anons', 'text'], 'string'],
            [['datatime'], 'safe'],
            [['title', 'image_path'], 'string', 'max' => 255],
            [['title'], 'unique'],
            [['image'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 2 * 1024 * 1024, 'on' => 'default'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxSize' => 2 * 1024 * 1024, 'on' => 'post-update']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'anons' => 'Anons',
            'text' => 'Text',
            'datatime' => 'Datatime',
            'image_path' => 'Image Path',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostTags()
    {
        return $this->hasMany(PostTag::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('post_tag', ['post_id' => 'id']);
    }

    public function beforeValidate()
    {
        $this->image = UploadedFile::getInstanceByName('image');

        return parent::beforeValidate();
    }

    public function afterValidate()
    {
        if ($this->image) {
            $this->image_path = 'post_images/' . uniqid() . '.' . $this->image->extension;
        }

        parent::afterValidate();
    }

    public function afterSave($insert, $changedAttributes) {
        // Сохранение изображения
        if ($this->image_path) {
            $this->image->saveAs($this->image_path);
        }

        // Добавление тегов к посту
        $data = Yii::$app->request->post();
        if (isset($data['tags'])) {
            PostTag::deleteAll([ 'post_id' => $this->id ]);

            $tags = explode(',', $data['tags']);
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    $tag = trim($tag);

                    if (!$tagModel = Tag::findOne(['name' => $tag])) {
                        $tagModel = new Tag();
                        $tagModel->name = $tag;
                        $tagModel->save();
                    }

                    $postTag = new PostTag();
                    $postTag->post_id = $this->id;
                    $postTag->tag_id = $tagModel->id;
                    $postTag->save();
                }
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        unlink($this->image_path);

        parent::afterDelete();
    }
}
