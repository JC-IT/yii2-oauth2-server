<?php
declare(strict_types=1);

namespace JCIT\oauth2\bridges;

use JCIT\oauth2\Module;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        if (Module::getInstance()->hasScope($identifier)) {
            return new Scope($identifier);
        }

        return null;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        if (!in_array($grantType, ['password', 'client_credentials'])) {
            foreach ($scopes as $key => $scope) {
                if (trim($scope) === '*') {
                    unset($scopes[$key]);
                }
            }
        }

        foreach ($scopes as $key => $scope) {
            if (!Module::getInstance()->hasScope($scope)) {
                unset($scopes[$key]);
            }
        }

        return $scopes;
    }
}
