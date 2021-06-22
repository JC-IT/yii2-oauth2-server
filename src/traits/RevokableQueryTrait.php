<?php
declare(strict_types=1);

namespace JCIT\oauth2\traits;

use yii\db\Expression;

trait RevokableQueryTrait
{
    public function notRevoked(): self
    {
        return $this->andWhere([
            'OR',
            ['revokedAt' => null],
            ['>=', 'revokedAt', new Expression('NOW()')],
        ]);
    }

    public function revoked(): self
    {
        return $this->andWhere(['not', ['revokedAt' => null]]);
    }
}
