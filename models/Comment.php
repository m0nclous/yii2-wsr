<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $post_id
 * @property string $author_name
 * @property string $comment
 * @property string $datatime
 *
 * @property User $authorName
 * @property Post $post
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id', 'author_name', 'comment'], 'required'],
            [['post_id'], 'integer'],
            [['datatime'], 'safe'],
            [['author_name', 'comment'], 'string', 'max' => 255],
            [['author_name'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_name' => 'login']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'author_name' => 'Author Name',
            'comment' => 'Comment',
            'datatime' => 'Datatime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthorName()
    {
        return $this->hasOne(User::className(), ['login' => 'author_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }
}
