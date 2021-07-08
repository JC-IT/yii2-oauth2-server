<?php
declare(strict_types=1);

namespace JCIT\oauth2\components\authMethods;

use JCIT\oauth2\Module;
use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class HttpBearerAuth extends AuthMethod
{
    public string $realm = 'api';

    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }

    protected function getAccessTokenRepository(): AccessTokenRepositoryInterface
    {
        return Module::getInstance()->accessTokenRepository;
    }

    protected function getAuthorizationValidator(): AuthorizationValidatorInterface
    {
        return new BearerTokenValidator($this->getAccessTokenRepository());
    }

    protected function getTokenType()
    {
        return 'Bearer';
    }
}
