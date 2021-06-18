<?php
declare(strict_types=1);

namespace JCIT\oauth2\bridges;

use DateTimeImmutable;
use JCIT\oauth2\events\AccessTokenCreated;
use JCIT\oauth2\traits\EventDispatchTrait;
use JCIT\oauth2\traits\FormatScopesForStorageTrait;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use EventDispatchTrait;
    use FormatScopesForStorageTrait;

    public function __construct(
        protected \JCIT\oauth2\repositories\AccessTokenRepository $accessTokenRepository,
        protected \JCIT\oauth2\repositories\ClientRepository $clientRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessToken
    {
        return new AccessToken($userIdentifier, $scopes, $clientEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        return $this->accessTokenRepository->isRevoked($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this->accessTokenRepository->create([
            'identifier' => $accessTokenEntity->getIdentifier(),
            'userId' => $accessTokenEntity->getUserIdentifier(),
            'clientId' => $this->clientRepository->fetch($accessTokenEntity->getClient()->getIdentifier())->id,
            'scopes' => $this->scopesToArray($accessTokenEntity->getScopes()),
            'expiresAt' => $accessTokenEntity->getExpiryDateTime()->format(DateTimeImmutable::ATOM),
        ]);

        $this->dispatch(AccessTokenCreated::EVENT, new AccessTokenCreated(
            $accessTokenEntity->getIdentifier(),
            $accessTokenEntity->getUserIdentifier(),
            $accessTokenEntity->getClient()->getIdentifier()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId): void
    {
        $this->accessTokenRepository->revoke($tokenId);
    }
}
