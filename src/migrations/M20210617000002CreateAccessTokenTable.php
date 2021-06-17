<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

use JCIT\oauth2\Module;
use yii\db\Connection;
use yii\db\Migration;

class M20210617000002CreateAccessTokenTable extends Migration
{
    protected function getDb(): Connection
    {
        return Module::getInstance()->getDb();
    }

    public function up(): void
    {
        $this->createTable(
            '{{%access_token}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string(100)->notNull(),
                'userId' => $this->integer()->null(),
                'clientId' => $this->integer()->notNull(),
                'name' => $this->string()->null(),
                'scopes' => $this->json()->null(),
                'createdAt' => $this->timestamp()->null(),
                'updatedAt' => $this->timestamp()->null(),
                'expiresAt' => $this->timestamp()->null(),
                'revokedAt' => $this->timestamp()->null(),
            ]
        );

        $this->createIndex('i-access_token-identifier', '{{%access_token}}', ['identifier']);
        $this->createIndex('i-access_token-userId', '{{%access_token}}', ['userId']);
        $this->addForeignKey('fk-access_token-clientId-client-id', '{{%access_token}}', ['clientId'], '{{%client}}', ['id']);
    }

    public function down(): void
    {
        $this->dropTable('{{%access_token}}');
    }
}
