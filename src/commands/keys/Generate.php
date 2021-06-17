<?php
declare(strict_types=1);

namespace JCIT\oauth2\commands\keys;

use JCIT\oauth2\Module;
use yii\base\Action;
use yii\console\ExitCode;

class Generate extends Action
{
    public $sslConfig = [
        'default_md' => 'sha256',
        'private_key_bits' => 2048,
    ];

    public function run(
        bool $force = false
    ) {
        $module = Module::getInstance();

        if (
            (
                file_exists($module->keyPath . DIRECTORY_SEPARATOR . 'oauth2-encryption.key')
                || file_exists($module->keyPath . DIRECTORY_SEPARATOR . 'oauth2-private.key')
                || file_exists($module->keyPath . DIRECTORY_SEPARATOR . 'oauth2-public.key')
            ) && !$force
        ) {
            $this->controller->stderr('Encryption keys already exist. Use the --force option to overwrite them.');
            return ExitCode::UNSPECIFIED_ERROR;
        } else {
            $sslConfig = [
                'private_key_bits'=> 2048,
                'default_md' => 'sha256',
            ];
            $ssl = openssl_pkey_new($sslConfig);
            openssl_pkey_export_to_file($ssl, $module->keyPath . DIRECTORY_SEPARATOR . 'oauth2-private.key');

            $public = openssl_pkey_get_details($ssl)['key'];
            file_put_contents($module->keyPath . DIRECTORY_SEPARATOR . 'oauth2-public.key', $public);

            file_put_contents($module->keyPath . DIRECTORY_SEPARATOR . 'oauth2-encryption.key', base64_encode(random_bytes(32)));
        }

        return ExitCode::OK;
    }
}
