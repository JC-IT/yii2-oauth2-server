<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers;

use JCIT\oauth2\controllers\token\Create;
use JCIT\oauth2\models\activeRecord\AccessToken;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\rest\OptionsAction;

class TokenController extends ActiveController
{
    public $modelClass = AccessToken::class;

    public function actions(): array
    {
        return [
            'create' => [
                'class' => Create::class,
            ],
            'options' => [
                'class' => OptionsAction::class
            ]
        ];
    }

    public function behaviors(): array
    {
        $result = parent::behaviors();

        $result['cors'] = [
            'class' => Cors::class,
        ];

        $result['authenticator']['optional'] = [
            'create',
            'options'
        ];

        return $result;
    }
}
