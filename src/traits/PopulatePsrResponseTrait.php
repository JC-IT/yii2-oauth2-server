<?php
declare(strict_types=1);

namespace JCIT\oauth2\traits;

use Psr\Http\Message\ResponseInterface;
use yii\web\Response as YiiResponse;

trait PopulatePsrResponseTrait
{
    protected function convertResponse(ResponseInterface $psrResponse, YiiResponse $yiiResponse): void
    {
        $yiiResponse->data = $psrResponse->getBody();
        $yiiResponse->setStatusCode($psrResponse->getStatusCode());
        $headers = $yiiResponse->getHeaders();
        foreach ($psrResponse->getHeaders() as $key => $value) {
            $headers->set($key, $value);
        }
    }
}
