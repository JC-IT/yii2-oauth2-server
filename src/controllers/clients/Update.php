<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers\clients;

use JCIT\oauth2\models\activeRecord\Client;
use JCIT\oauth2\models\form\clients\Update as ClientUpdate;
use JCIT\oauth2\repositories\ClientRepository;
use yii\base\Action;
use yii\base\Security;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\User;

class Update extends Action
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
        int $id
    ) {
        $client = $this->clientRepository->fetch($id);
        if (!$client) {
            throw new NotFoundHttpException('Client not found.');
        }

        if (isset($this->accessCheck)) {
            ($this->accessCheck)($user, $client);
        }

        $model = new ClientUpdate($client, $security);

        if ($request->isPut && $model->load($request->bodyParams) && $model->run()) {
            return $this->controller->redirect(['index']);
        }

        return $this->controller->render(
            'update',
            [
                'model' => $model,
            ]
        );
    }
}
