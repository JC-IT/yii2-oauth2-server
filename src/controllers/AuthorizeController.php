<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers;

use JCIT\oauth2\controllers\authorize\Authorize;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class AuthorizeController extends Controller
{
    public function actions(): array
    {
        return [
            'authorize' => Authorize::class,
        ];
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'actions' => ['authorize'],
                            'allow' => true,
                        ],
                    ]
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'authorize' => ['GET', 'POST'],
                    ]
                ]
            ],
            parent::behaviors()
        );
    }
}
