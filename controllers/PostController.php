<?php

namespace app\controllers;

use app\models\Comment;
use app\models\Tag;
use app\models\User;
use yii\rest\ActiveController;
use app\models\Post;
use Yii;

class PostController extends ActiveController
{
    public $modelClass = 'app\models\Post';

    protected function verbs()
    {
        $verbs = parent::verbs();

        $verbs['update'] = ['POST'];

        return $verbs;
    }

    public function behaviors()
    {
        $beheviors = parent::behaviors();

        $beheviors['bearerAuth'] = [
            'class' => HttpBearerAuthHelper::className(),
            'except' => ['auth', 'index', 'view', 'add-comment'],
            'optional' => ['add-comment'],
        ];

        return $beheviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['index']);
        unset($actions['view']);

        return $actions;
    }

    public function actionCreate()
    {
        $post = new Post();
        $data = Yii::$app->request->post();

        if ($post->load($data, '') && $post->validate() && $post->save()) {
            Yii::$app->response->setStatusCode(201, 'Successful creation');
            return [
                'status' => true,
                'post_id' => $post->id,
            ];
        }

        Yii::$app->response->setStatusCode(400, 'Creating error');
        return [
            'status' => false,
            'message' => $post->firstErrors,
        ];
    }

    public function actionUpdate($id)
    {
        $post = Post::findOne($id);
        $data = Yii::$app->request->post();

        if ($post) {
            $post->scenario = 'post-update';

            if ($post->load($data, '') && $post->validate() && $post->save()) {
                Yii::$app->response->setStatusCode(201, 'Successful creation');
                return [
                    'status' => true,
                    'post' => $post,
                ];
            }

            Yii::$app->response->setStatusCode(400, 'Editing error');
            return [
                'status' => false,
                'message' => $post->firstErrors,
            ];
        }

        Yii::$app->response->setStatusCode(404, 'Post not found');
        return [
            'message' => 'Post not found'
        ];
    }

    public function actionDelete($id)
    {
        $post = Post::findOne($id);

        if ($post) {
            $post->delete();

            Yii::$app->response->setStatusCode(201, 'Successful delete');
            return [
                'status' => true
            ];
        }

        Yii::$app->response->setStatusCode(404, 'Post not found');
        return [
            'message' => 'Post not found'
        ];
    }

    public function actionIndex()
    {
        Yii::$app->response->setStatusCode(200, 'List posts');
        return Post::find()->all();
    }

    public function actionView($id)
    {
        $post = Post::findOne($id);

        if ($post) {
            $post->scenario = 'with-comments';

            Yii::$app->response->setStatusCode(200, 'View post');
            return $post;
        }

        Yii::$app->response->setStatusCode(404, 'Post not found');
        return [
            'message' => 'Post not found'
        ];
    }

    public function actionAddComment($id)
    {
        $post = Post::findOne($id);
        $data = Yii::$app->request->post();

        if ($post) {
            $comment = new Comment();

            if(!Yii::$app->user->isGuest) {
                $data['author'] = 'admin';
            }

            $data['post_id'] = $post->id;
            if($comment->load($data, '') && $comment->validate() && $comment->save()) {
                Yii::$app->response->setStatusCode(201, 'Successful creation');
                return [
                    'status' => true,
                ];
            }

            Yii::$app->response->setStatusCode(400, 'Creation error');
            return [
                'status' => false,
                'message' => $comment->firstErrors,
            ];
        }

        Yii::$app->response->setStatusCode(404, 'Post not found');
        return [
            'message' => 'Post not found'
        ];
    }

    public function actionDeleteComment($id, $comment_id)
    {
        $post = Post::findOne($id);

        if ($post) {
            if($comment = Comment::findOne($comment_id)) {
                $comment->delete();

                Yii::$app->response->setStatusCode(201, 'Successful delete');
                return [
                    'status' => true,
                ];
            }

            Yii::$app->response->setStatusCode(404, 'Comment not found');
            return [
                'message' => 'Comment not found',
            ];
        }

        Yii::$app->response->setStatusCode(404, 'Post not found');
        return [
            'message' => 'Post not found'
        ];
    }

    public function actionSearchByTag($tag)
    {
        $posts = [];
        $tag = Tag::findOne(['name' => $tag]);

        if ($tag) {
            $posts = $tag->posts;
        }

        Yii::$app->response->setStatusCode(200, 'Found posts');
        return $posts;
    }

    public function actionAuth()
    {
        $userForValidate = new User();
        $data = Yii::$app->request->post();

        if ($userForValidate->load($data, '')) {
            if ($user = User::findOne([
                'login' => $data['login'],
                'password' => $data['password'],
            ])) {
                $user->generateToken();

                Yii::$app->response->setStatusCode(200, 'Successful authorization');
                return [
                    'status' => true,
                    'token' => $user->token,
                ];
            }
        }

        Yii::$app->response->setStatusCode(401, 'Invalid authorization data');
        return [
            'status' => false,
            'message' => 'Invalid authorization data'
        ];
    }
}