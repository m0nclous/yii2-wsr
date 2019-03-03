<?php

namespace app\controllers;

use yii\rest\ActiveController;
use app\models\Post;
use Yii;

class PostController extends ActiveController
{
    public $modelClass = 'app\models\Post';

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);

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
}