<?php
declare(strict_types=1);

namespace JCIT\oauth2\repositories;

use JCIT\oauth2\exceptions\FailedSaveException;
use JCIT\oauth2\models\activeRecord\RefreshToken;
use yii\db\Expression;

class RefreshTokenRepository
{
    public function create($attributes): RefreshToken
    {
        $model = new RefreshToken($attributes);

        if (!$model->save()) {
            throw new FailedSaveException($model->errors);
        }

        return $model;
    }

    public function fetch(string $identifier): ?RefreshToken
    {
        return RefreshToken::findOne(['identifier' => $identifier]);
    }

    public function isRevoked(string $identifier): bool
    {
        $model = $this->fetch($identifier);

        return !$model || is_null($model->revokedAt);
    }

    public function revoke(string $identifier): void
    {
        RefreshToken::updateAll(['revokedAt' => new Expression('NOW()')], ['identifier' => $identifier]);
    }
}
