<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

use JCIT\oauth2\Module;
use yii\db\Connection;
use yii\db\Migration;

class M20210617000003CreateRefreshTokenTable extends Migration
{
    protected function getDb(): Connection
    {
        return Module::getInstance()->getDb();
    }

    public function up(): void
    {
        $this->createTable(
            '{{%refresh_token}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string(100)->notNull(),
                'accessTokenId' => $this->integer()->null(),
                'createdAt' => $this->timestamp()->null(),
                'updatedAt' => $this->timestamp()->null(),
                'expiresAt' => $this->timestamp()->null(),
                'revokedAt' => $this->timestamp()->null(),
            ]
        );

        $this->createIndex('i-refresh_token-identifier', '{{%refresh_token}}', ['identifier']);
        $this->addForeignKey('fk-refresh_token-accessTokenId-access_token-id', '{{%refresh_token}}', ['accessTokenId'], '{{%access_token}}', ['id']);
    }

    public function down(): void
    {
        $this->dropTable('{{%refresh_token}}');
    }
}
