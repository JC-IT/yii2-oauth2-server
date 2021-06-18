<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

use JCIT\oauth2\Module;

class Migration extends \yii\db\Migration
{
    public function init()
    {
        $module = Module::getInstance();

        if (!is_null($module)) {
            $this->db = Module::getInstance()->db;
        }

        parent::init();
    }
}
