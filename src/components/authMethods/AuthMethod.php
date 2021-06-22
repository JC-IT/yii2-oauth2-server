<?php
declare(strict_types=1);

namespace JCIT\oauth2\components\authMethods;

use JCIT\oauth2\components\ServerRequest;
use JCIT\oauth2\Module;
use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;

abstract class AuthMethod extends \yii\filters\auth\AuthMethod
{
    public function authenticate($user, $request, $response)
    {
        if (!$this->tokenTypeExists($request)) {
            return null;
        }

        $accessTokenRepository = $this->getAccessTokenRepository();

        return $this->validate(
            new ResourceServer(
                $accessTokenRepository,
                $this->getPublicKey(),
                $this->getAuthorizationValidator()
            ),
            new ServerRequest(
                $this->request ?: \Yii::$app->getRequest()
            ),
            $this->response ?: \Yii::$app->getResponse(),
            $this->user ?: \Yii::$app->getUser()
        );
    }

    protected abstract function getAccessTokenRepository(): AccessTokenRepositoryInterface;
    protected abstract function getAuthorizationValidator(): AuthorizationValidatorInterface;

    protected function getPublicKey(): CryptKey
    {
        return Module::getInstance()->createPublicCryptKey();
    }

    public function handleFailure($response)
    {
        throw OAuthServerException::accessDenied();
    }
}
