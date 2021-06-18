<?php
declare(strict_types=1);

namespace JCIT\oauth2\events;

use yii\base\Event;

class AuthCodeCreated extends Event
{
    const EVENT = 'authCodeCreated';

    public function __construct(
        private string $tokenIdentifier,
        private $userIdentifier,
        private string $clientIdentifier,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function getClientIdentifier(): string
    {
        return $this->clientIdentifier;
    }

    public function getTokenIdentifier(): string
    {
        return $this->tokenIdentifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
