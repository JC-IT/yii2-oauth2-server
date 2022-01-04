<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\form\clients;

use JCIT\oauth2\models\activeRecord\Client;

/**
 * @property Client $model
 * @method Client getModel()
 */
abstract class ActiveForm extends \JCIT\models\form\ActiveForm
{
    protected string $baseModelClass = Client::class;
}
