<?php
declare(strict_types=1);

namespace JCIT\oauth2\repositories;

use JCIT\oauth2\Module;
use JCIT\oauth2\objects\Scope;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class ScopeRepository
{
    public function __construct(
        protected AccessTokenRepository $accessTokenRepository,
        protected ClientRepository $clientRepository,
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

        $client = $this->clientRepository->fetchByIdentifier($authorizationRequest->getClient()->getIdentifier());

        $defaultScopes = in_array('*', $client->defaultScopes) ? array_keys($module->scopes) : $client->defaultScopes;
        foreach ($defaultScopes as $defaultScope) {
            if ($scope = $this->fetch($module, $defaultScope)) {
                $scopes[$scope->getIdentifier()] = $scope;
            }
        }

        foreach ($authorizationRequest->getScopes() as $scope) {
            if ($scope = $this->fetch($module, $scope->getIdentifier())) {
                $scopes[$scope->getIdentifier()] = $scope;
            }
        }

        $allowedScopeIdentifiers = in_array('*', $client->allowedScopes) ? array_keys($module->scopes) : $client->allowedScopes;
        $scopes = array_filter($scopes, function(Scope $scope) use ($allowedScopeIdentifiers) {
            return in_array($scope->getIdentifier(), $allowedScopeIdentifiers);
        });

        return $scopes;
    }
}
