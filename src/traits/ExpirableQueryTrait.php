<?php
declare(strict_types=1);

namespace JCIT\oauth2\traits;

use yii\db\Expression;

trait ExpirableQueryTrait
{
    public function notExpired(): self
    {
        return $this->andWhere([
            'OR',
            ['expiresAt' => null],
            ['>=', 'expiresAt', new Expression('NOW()')],
        ]);
    }

    public function expired(): self
    {
        return $this->andWhere(['<', 'expiresAt', new Expression('NOW()')]);
    }
}
