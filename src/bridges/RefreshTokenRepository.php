<?php
declare(strict_types=1);

namespace JCIT\oauth2\bridges;

use DateTimeImmutable;
use JCIT\oauth2\events\RefreshTokenCreated;
use JCIT\oauth2\traits\EventDispatchTrait;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    use EventDispatchTrait;

    public function __construct(
        protected \JCIT\oauth2\repositories\AccessTokenRepository $accessTokenRepository,
        protected \JCIT\oauth2\repositories\RefreshTokenRepository $refreshTokenRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken(): RefreshToken
    {
        return new RefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        return $this->refreshTokenRepository->isRevoked($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $this->refreshTokenRepository->create([
            'identifier' => $refreshTokenEntity->getIdentifier(),
            'accessTokenId' => $this->accessTokenRepository->fetch($refreshTokenEntity->getAccessToken()->getIdentifier())->id,
            'expiresAt' => $refreshTokenEntity->getExpiryDateTime()->format(DateTimeImmutable::ATOM),
        ]);

        $this->dispatch(RefreshTokenCreated::EVENT, new RefreshTokenCreated(
            $refreshTokenEntity->getIdentifier(),
            $refreshTokenEntity->getAccessToken()->getIdentifier(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId): void
    {
        $this->accessTokenRepository->revoke($tokenId);
    }
}
