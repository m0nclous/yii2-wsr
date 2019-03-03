<?php

namespace app\controllers;

use Yii;
use yii\filters\auth\HttpBearerAuth;

class HttpBearerAuthHelper extends HttpBearerAuth {
    public function handleFailure($response)
    {
        Yii::$app->response->setStatusCode(401, 'Unauthorized');
        Yii::$app->response->data = [
            'message' => 'Unauthorized',
        ];
    }
}
