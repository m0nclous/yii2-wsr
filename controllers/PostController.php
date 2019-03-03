<?php

namespace app\controllers;

use yii\rest\ActiveController;

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

    }
}