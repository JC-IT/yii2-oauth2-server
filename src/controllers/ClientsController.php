<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers;

use JCIT\oauth2\controllers\clients\Create;
use JCIT\oauth2\controllers\clients\Delete;
use JCIT\oauth2\controllers\clients\Index;
use JCIT\oauth2\controllers\clients\Update;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class ClientsController extends Controller
{
    public string|null $accessRole = null;

    public function actions(): array
    {
        return [
            'create' => Create::class,
            'delete' => Delete::class,
            'index' => Index::class,
            'update' => Update::class,
        ];
    }

    public function behaviors(): array
    {
        $result = [];

        if ($this->accessRole) {
            $result = ArrayHelper::merge($result, [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => [
                                'create',
                                'delete',
                                'index',
                                'update',
                            ],
                            'roles' => [$this->accessRole],
                        ],
                    ]
                ],
                'verb' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'create' => ['GET', 'POST'],
                        'delete' => ['DELETE'],
                        'index' => ['GET'],
                        'update' => ['GET', 'PUT'],
                    ]
                ]
            ]);
        }

        return $result;
    }

    public function init()
    {
        $this->accessRole = $this->accessRole ?? $this->module->manageClientsRole;

        parent::init();
    }
}
