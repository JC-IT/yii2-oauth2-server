<?php
declare(strict_types=1);

namespace JCIT\oauth2\models;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use yii\base\Model;
use yii\validators\BooleanValidator;
use yii\validators\DefaultValueValidator;
use yii\validators\RequiredValidator;

class Authorize extends Model
{
    public bool $accept = false;

    public function __construct(
        protected AuthorizationRequest $authorizationRequest,
        protected UserEntityInterface $userEntity,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function getClient(): ClientEntityInterface
    {
        return $this->authorizationRequest->getClient();
    }

    public function getScopes(): array
    {
        return $this->authorizationRequest->getScopes();
    }

    public function rules(): array
    {
        return [
            [['accept'], RequiredValidator::class],
            [['accept'], DefaultValueValidator::class, 'value' => false],
            [['accept'], BooleanValidator::class],
        ];
    }
}
