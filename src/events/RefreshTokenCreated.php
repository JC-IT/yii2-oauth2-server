<?php
declare(strict_types=1);

namespace JCIT\oauth2\events;

use yii\base\Event;

class RefreshTokenCreated extends Event
{
    const EVENT = 'refreshTokenCreated';

    public function __construct(
        private string $accessTokenIdentifier,
        private string $refreshTokenIdentifier,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function getAccessTokenIdentifier(): string
    {
        return $this->accessTokenIdentifier;
    }

    public function getRefreshTokenIdentifier(): string
    {
        return $this->refreshTokenIdentifier;
    }
}
