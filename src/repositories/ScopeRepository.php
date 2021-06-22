<?php
declare(strict_types=1);

namespace JCIT\oauth2\repositories;

use JCIT\oauth2\Module;
use JCIT\oauth2\objects\Scope;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class ScopeRepository
{
    public function __construct(
        protected AccessTokenRepository $accessTokenRepository
    ) {
    }


    public function fetch(Module $module, string $identifier): ?Scope
    {
        if (isset($module->scopes[$identifier])) {
            return new Scope($identifier, $module->scopes[$identifier]);
        }

        return null;
    }

    /**
     * @return Scope[]
     */
    public function resolveForAuthorizationRequest(Module $module, AuthorizationRequest $authorizationRequest): array
    {
        $scopes = [];
        foreach ($authorizationRequest->getScopes() as $scope) {
            $scopes[] = $this->fetch($module, $scope->getIdentifier());
        }

        return $scopes;
    }
}
