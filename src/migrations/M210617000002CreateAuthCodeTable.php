<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

class M210617000002CreateAuthCodeTable extends Migration
{
    public function up(): void
    {
        $this->createTable(
            '{{%auth_code}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string(100)->notNull(),
                'userId' => $this->integer()->null(),
                'clientId' => $this->integer()->notNull(),
                'scopes' => $this->json()->null(),
                'createdAt' => $this->timestamp()->null(),
                'updatedAt' => $this->timestamp()->null(),
                'expiresAt' => $this->timestamp()->null(),
                'revokedAt' => $this->timestamp()->null(),
            ]
        );

        $this->createIndex('i-auth_code-identifier', '{{%auth_code}}', ['identifier']);
        $this->createIndex('i-auth_code-userId', '{{%auth_code}}', ['userId']);
        $this->addForeignKey('fk-auth_code-clientId-client-id', '{{%auth_code}}', ['clientId'], '{{%client}}', ['id'], 'CASCADE', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%auth_code}}');
    }
}
