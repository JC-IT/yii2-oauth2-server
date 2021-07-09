<?php
declare(strict_types=1);

namespace JCIT\oauth2\bridges;

use DateTimeImmutable;
use JCIT\oauth2\events\AuthCodeCreated;
use JCIT\oauth2\traits\EventDispatchTrait;
use JCIT\oauth2\traits\FormatScopesForStorageTrait;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use EventDispatchTrait;
    use FormatScopesForStorageTrait;

    public function __construct(
        protected \JCIT\oauth2\repositories\AuthCodeRepository $authCodeRepository,
        protected \JCIT\oauth2\repositories\ClientRepository $clientRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode(): AuthCode
    {
        return new AuthCode();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        return $this->authCodeRepository->isRevoked($codeId);
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $this->authCodeRepository->create([
            'identifier' => $authCodeEntity->getIdentifier(),
            'userId' => $authCodeEntity->getUserIdentifier(),
            'clientId' => $this->clientRepository->fetchByIdentifier($authCodeEntity->getClient()->getIdentifier())->id,
            'scopes' => $this->scopesToArray($authCodeEntity->getScopes()),
            'expiresAt' => $authCodeEntity->getExpiryDateTime()->format(DateTimeImmutable::ATOM),
        ]);

        $this->dispatch(AuthCodeCreated::EVENT, new AuthCodeCreated(
            $authCodeEntity->getIdentifier(),
            $authCodeEntity->getUserIdentifier(),
            $authCodeEntity->getClient()->getIdentifier()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $this->authCodeRepository->revoke($codeId);
    }
}
