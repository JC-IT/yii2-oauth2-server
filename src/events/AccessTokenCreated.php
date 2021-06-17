<?php
declare(strict_types=1);

namespace JCIT\oauth2\events;

use yii\base\Event;

class AccessTokenCreated extends Event
{
    const EVENT = 'accessTokenCreated';

    public function __construct(
        private string $accessTokenIdentifier,
        private string $userIdentifier,
        private string $clientIdentifier,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function getClientIdentifier(): string
    {
        return $this->clientIdentifier;
    }

    public function getAccessTokenIdentifier(): string
    {
        return $this->accessTokenIdentifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
