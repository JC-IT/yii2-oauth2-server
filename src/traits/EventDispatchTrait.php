<?php
declare(strict_types=1);

namespace JCIT\oauth2\traits;

use JCIT\oauth2\Module;
use yii\base\Event;

trait EventDispatchTrait
{
    public function dispatch(string $eventName, Event $event): void
    {
        Event::trigger(Module::class, $eventName, $event);
    }
}
