<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

use JCIT\oauth2\Module;
use yii\db\Connection;
use yii\db\Migration;

class M20210617000004CreateClientTable extends Migration
{
    protected function getDb(): Connection
    {
        return Module::getInstance()->getDb();
    }

    public function up(): void
    {
        $this->createTable(
            '{{%client}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string(100)->notNull(),
                'name' => $this->string()->notNull(),
                'secret' => $this->string()->null(),
                'redirect' => $this->json(),
                'passwordClient' => $this->boolean(),
                'createdAt' => $this->timestamp()->null(),
                'updatedAt' => $this->timestamp()->null(),
                'revokedAt' => $this->timestamp()->null(),
            ]
        );

        $this->createIndex('i-client-identifier', '{{%client}}', ['identifier']);
    }

    public function down(): void
    {
        $this->dropTable('{{%client}}');
    }
}
