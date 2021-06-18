<?php
declare(strict_types=1);

namespace JCIT\oauth2\components;

use yii\web\Request;

class ServerRequest extends \GuzzleHttp\Psr7\ServerRequest
{
    public function __construct(
       Request $request
    ) {
        parent::__construct(
            $request->method,
            $request->url,
            $request->headers->toArray(),
            $request->rawBody
        );
    }
}
