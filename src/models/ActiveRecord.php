<?php
declare(strict_types=1);

namespace JCIT\oauth2\models;

use JCIT\oauth2\Module;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return Module::getInstance()->getDb();
    }
}
