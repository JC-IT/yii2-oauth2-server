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
        protected \JCIT\oauth2\repositories\ScopeRepository $scopeRepository
    ) {
    }

    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        $scope = $this->scopeRepository->fetch(Module::getInstance(), $identifier);

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
        \Yii::debug($scopes);
        if (!in_array($grantType, ['password', 'client_credentials'])) {
            foreach ($scopes as $key => $scope) {
                if ($scope->getIdentifier() === '*') {
                    unset($scopes[$key]);
                }
            }
        }

        foreach ($scopes as $key => $scope) {
            if (!Module::getInstance()->hasScope($scope->getIdentifier())) {
                unset($scopes[$key]);
            }
        }

        return $scopes;
    }
}
