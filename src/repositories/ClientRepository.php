<?php
declare(strict_types=1);

namespace JCIT\oauth2\repositories;

use JCIT\oauth2\models\activeRecord\Client;
use yii\db\ActiveQuery;

class ClientRepository
{
    public function createNew(): Client
    {
        return new Client();
    }

    public function fetch(int $id): ?Client
    {
        return Client::findOne(['id' => $id]);
    }

    public function fetchByIdentifier(string $identifier): ?Client
    {
        return Client::findOne(['identifier' => $identifier]);
    }

    public function fetchActiveByIdentifier(string $identifier): ?Client
    {
        $client = $this->fetchByIdentifier($identifier);
        return $client && is_null($client->revokedAt) ? $client : null;
    }

    public function find(): ActiveQuery
    {
        return Client::find();
    }
}
