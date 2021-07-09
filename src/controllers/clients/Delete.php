<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers\clients;

use JCIT\oauth2\repositories\ClientRepository;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\User;

class Delete extends Action
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
        int $id,
    ) {
        $client = $this->clientRepository->fetch($id);
        if (!$client) {
            throw new NotFoundHttpException('Client not found.');
        }

        if (isset($this->accessCheck)) {
            ($this->accessCheck)($user, $client);
        }

        // Preferred to use isDelete but the default action column of Yii sends post request
        if ($request->isPost) {
            $client->delete();
        }

        return $this->controller->redirect(['index']);
    }
}
