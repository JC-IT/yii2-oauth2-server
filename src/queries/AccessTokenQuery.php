<?php
declare(strict_types=1);

namespace JCIT\oauth2\queries;

use JCIT\oauth2\traits\ExpirableQueryTrait;
use JCIT\oauth2\traits\RevokableQueryTrait;
use yii\db\ActiveQuery;

class AccessTokenQuery extends ActiveQuery
{
    use ExpirableQueryTrait;
    use RevokableQueryTrait;
}
