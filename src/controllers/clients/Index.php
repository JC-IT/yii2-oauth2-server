<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers\clients;

use JCIT\oauth2\models\search\Client as ClientSearch;
use JCIT\oauth2\repositories\ClientRepository;
use yii\base\Action;
use yii\web\Request;
use yii\web\User;

class Index extends Action
{
    public \Closure|null $accessCheck = null;

    public function __construct(
        $id,
        $controller,
        protected ClientRepository $clientRepository,
        $config = []
    ) {
        parent::__construct($id, $controller, $config);
    }

    public function run(
        Request $request,
        User $user,
    ) {
        if (isset($this->accessCheck)) {
            ($this->accessCheck)($user);
        }

        $searchModel = new ClientSearch($this->clientRepository->find());

        $searchModel->load($request->queryParams);

        return $this->controller->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $searchModel->search(),
            ]
        );
    }
}
