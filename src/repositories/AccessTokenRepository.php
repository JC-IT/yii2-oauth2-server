<?php
declare(strict_types=1);

namespace JCIT\oauth2\repositories;

use JCIT\oauth2\exceptions\FailedSaveException;
use JCIT\oauth2\models\activeRecord\AccessToken;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use yii\db\Expression;

class AccessTokenRepository
{
    public string $accessTokenClass = AccessToken::class;

    public function __construct(
        protected ClientRepository $clientRepository
    ) {
    }

    public function create($attributes): AccessToken
    {
        $model = new ($this->accessTokenClass)($attributes);

        if (!$model->save()) {
            throw new FailedSaveException($model->errors);
        }

        return $model;
    }

    public function fetch(string $identifier): ?AccessToken
    {
        return $this->accessTokenClass::findOne(['identifier' => $identifier]);
    }

    public function fetchValidForUser(UserEntityInterface $user, ClientEntityInterface $client): ?AccessToken
    {
        return $this->accessTokenClass::find()
            ->andWhere([
                'userId' => $user->getIdentifier(),
                'clientId' => $this->clientRepository->fetch($client->getIdentifier())?->id,
            ])
            ->notRevoked()
            ->notExpired()
            ->orderBy(['expiresAt' => SORT_DESC])
            ->one();
    }

    public function isRevoked(string $identifier): bool
    {
        $model = $this->fetch($identifier);

        return !$model || !is_null($model->revokedAt);
    }

    public function revoke(string $identifier): void
    {
        $this->accessTokenClass::updateAll(['revokedAt' => new Expression('NOW()')], ['identifier' => $identifier]);
    }
}
