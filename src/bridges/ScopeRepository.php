<?php
declare(strict_types=1);

namespace JCIT\oauth2\bridges;

use JCIT\oauth2\Module;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    public function __construct(
        protected \JCIT\oauth2\repositories\ClientRepository $clientRepository,
        protected \JCIT\oauth2\repositories\ScopeRepository $scopeRepository,
    ) {
    }

    protected function getModule(): Module
    {
        return Module::getInstance();
    }

    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        $scope = $this->scopeRepository->fetch($this->getModule(), $identifier);

        return $scope ? new Scope($scope->getIdentifier()) : null;
    }

    /**
     * @param Scope[] $scopes
     * @return Scope[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $client = $this->clientRepository->fetchByIdentifier($clientEntity->getIdentifier());
        $module = $this->getModule();
        $result = [];

        $defaultScopes = in_array('*', $client->defaultScopes) ? array_keys($module->scopes) : $client->defaultScopes;
        foreach ($defaultScopes as $defaultScope) {
            if ($scope = $this->getScopeEntityByIdentifier($defaultScope)) {
                $result[$scope->getIdentifier()] = $scope;
            }
        }

        foreach ($scopes as $requestedScope) {
            $result[$requestedScope->getIdentifier()] = $requestedScope;
        }

        $allowedScopeIdentifiers = in_array('*', $client->allowedScopes) ? array_keys($module->scopes) : $client->allowedScopes;
        $result = array_filter($result, function(Scope $scope) use ($allowedScopeIdentifiers) {
            return in_array($scope->getIdentifier(), $allowedScopeIdentifiers);
        });

        return $result;
    }
}
