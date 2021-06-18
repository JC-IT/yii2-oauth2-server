<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers\revoke;

use JCIT\jobqueue\exceptions\OAuthHttpException;
use JCIT\oauth2\models\activeRecord\AccessToken;
use JCIT\oauth2\Module;
use League\OAuth2\Server\Exception\OAuthServerException;
use yii\helpers\Json;
use yii\rest\Action;
use yii\web\ServerErrorHttpException;

class Create extends Action
{
    public $modelClass = AccessToken::class;

    public function run()
    {
        /** @var Module $module */
        $module = $this->controller->module;

        try {
            $response = $module->getAuthorizationServer()
                ->respondToRevokeTokenRequest(
                    $module->getServerRequest(),
                    $module->getServerResponse()
                );
            return Json::decode($response->getBody()->__toString());
        } catch (OAuthServerException $exception) {
            throw new OAuthHttpException($exception);
        } catch (\Exception $exception) {
            throw new ServerErrorHttpException(
                'Unable to respond to revoke token request.', 0,
                YII_DEBUG ? $exception : null
            );
        }
    }
}
