<?php
declare(strict_types=1);

namespace JCIT\oauth2\repositories;

use JCIT\oauth2\models\activeRecord\Client;

class ClientRepository
{
    public function fetch(string $identifier): ?Client
    {
        return Client::findOne(['identifier' => $identifier]);
    }

    public function fetchActive(string $identifier): ?Client
    {
        $client = $this->fetch($identifier);
        return $client && is_null($client->revokedAt) ? $client : null;
    }
}
