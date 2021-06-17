<?php
declare(strict_types=1);

namespace JCIT\oauth2\commands;

use JCIT\oauth2\commands\keys\Generate;
use yii\console\Controller;

class KeysController extends Controller
{
    public $defaultAction = 'generate';

    public function actions(): array
    {
        return [
            'generate' => Generate::class,
        ];
    }
}
