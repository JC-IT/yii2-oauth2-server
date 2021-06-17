<?php
declare(strict_types=1);

namespace JCIT\oauth2\bridges;

use JCIT\oauth2\models\activeRecord\Client as ActiveRecordClient;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(
        protected \JCIT\oauth2\repositories\ClientRepository $clientRepository,
    ) {
    }

    public function getClientEntity($clientIdentifier): ?Client
    {
        $client = $this->clientRepository->fetchActive($clientIdentifier);

        if (!$client) {
            return null;
        }

        return new Client(
            $client->identifier,
            $client->name,
            $client->redirectUris,
            $client->isConfidential,
        );
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->clientRepository->fetchActive($clientIdentifier);

        if (!$client || !$this->handlesGrant($client, $grantType)) {
            return false;
        }

        return !$client->isConfidential || $this->verifySecret((string) $clientSecret, $client->secret);
    }

    protected function handlesGrant(ActiveRecordClient $client, string $grantType): bool
    {
        return in_array($grantType, $client->grantTypes);
    }

    protected function verifySecret(string $clientSecret, string $storedHash)
    {
        return password_verify($clientSecret, $storedHash);
    }
}
