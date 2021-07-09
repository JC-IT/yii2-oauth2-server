<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers\clients;

use JCIT\oauth2\models\activeRecord\Client;
use JCIT\oauth2\models\form\clients\Create as ClientCreate;
use JCIT\oauth2\repositories\ClientRepository;
use yii\base\Action;
use yii\base\Security;
use yii\web\Request;
use yii\web\User;

class Create extends Action
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
        Security $security,
        User $user,
    ) {
        $client = $this->clientRepository->createNew();

        if (isset($this->accessCheck)) {
            ($this->accessCheck)($user, $client);
        }

        $model = new ClientCreate($client, $security);

        if ($request->isPost && $model->load($request->bodyParams) && $model->run()) {
            return $this->controller->redirect(['index']);
        }

        return $this->controller->render(
            'create',
            [
                'model' => $model,
            ]
        );
    }
}
