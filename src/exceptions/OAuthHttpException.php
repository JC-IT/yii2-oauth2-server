<?php
declare(strict_types=1);

namespace JCIT\oauth2\exceptions;

use League\OAuth2\Server\Exception\OAuthServerException;
use yii\web\HttpException;

class OAuthHttpException extends HttpException
{
    public function __construct(OAuthServerException $previous)
    {
        $hint = $previous->getHint();

        parent::__construct(
            $previous->getHttpStatusCode(),
            $hint ? $previous->getMessage() . ' ' . $hint . '.' : $previous->getMessage(),
            $previous->getCode(),
            YII_DEBUG === true ? $previous : null
        );
    }
}
