<?php
declare(strict_types=1);

namespace JCIT\oauth2\objects;

class Scope
{
    public function __construct(
        protected string $identifier,
        protected string $description,
    ) {
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
