<?php
declare(strict_types=1);

namespace JCIT\oauth2\exceptions;

use yii\web\ServerErrorHttpException;

class FailedSaveException extends ServerErrorHttpException
{
    public function __construct(array $errors, $message = null, $code = 0, \Exception $previous = null)
    {
        $message = $message ?? \Yii::t('app', 'Failed saving with errors: ' . print_r($errors, true));

        parent::__construct($message, $code, $previous);
    }
}
