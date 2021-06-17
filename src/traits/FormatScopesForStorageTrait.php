<?php
declare(strict_types=1);

namespace JCIT\oauth2\traits;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

trait FormatScopesForStorageTrait
{
    protected function formatScopesForStorage(array $scopes): array
    {
        return $this->scopesToArray($scopes);
    }

    public function scopesToArray(array $scopes)
    {
        return array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }
}
