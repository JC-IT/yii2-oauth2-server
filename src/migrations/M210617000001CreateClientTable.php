<?php
declare(strict_types=1);

namespace JCIT\oauth2\migrations;

class M210617000001CreateClientTable extends Migration
{
    public function up(): void
    {
        $this->createTable(
            '{{%client}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string(100)->notNull(),
                'name' => $this->string()->notNull(),
                'secret' => $this->string()->null(),
                'redirectUris' => $this->json(),
                'grantTypes' => $this->json(),
                'scopes' => $this->json(),
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
