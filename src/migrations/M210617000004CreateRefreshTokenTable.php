<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

class M210617000004CreateRefreshTokenTable extends Migration
{
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
        $this->addForeignKey('fk-refresh_token-accessTokenId-access_token-id', '{{%refresh_token}}', ['accessTokenId'], '{{%access_token}}', ['id'], 'CASCADE', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%refresh_token}}');
    }
}
