<?php
declare(strict_types=1);

namespace JCIT\oauth2;

use DateInterval;
use Defuse\Crypto\Encoding;
use Defuse\Crypto\Key;
use iter\IterFnTest;
use JCIT\oauth2\bridges\AccessTokenRepository as BridgeAccessTokenRepository;
use JCIT\oauth2\bridges\AuthCodeRepository as BridgeAuthCodeRepository;
use JCIT\oauth2\bridges\ClientRepository as BridgeClientRepository;
use JCIT\oauth2\bridges\RefreshTokenRepository as BridgeRefreshTokenRepository;
use JCIT\oauth2\bridges\ScopeRepository;
use JCIT\oauth2\components\ServerRequest;
use JCIT\oauth2\components\ServerResponse;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;
use yii\rest\UrlRule;
use yii\web\GroupUrlRule;
use yii\web\Request;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public string|array|Connection $db = 'db';

    public array|string|AccessTokenRepositoryInterface $accessTokenRepository = BridgeAccessTokenRepository::class;
    public array|string|AuthCodeRepositoryInterface $authCodeRepository = BridgeAuthCodeRepository::class;
    public array|string|ClientRepositoryInterface $clientRepository = BridgeClientRepository::class;
    public array|string|RefreshTokenRepositoryInterface $refreshTokenRepository = BridgeRefreshTokenRepository::class;
    public array|string|ScopeRepositoryInterface $scopeRepository = ScopeRepository::class;
    public array|string|UserRepositoryInterface $userRepository = UserRepositoryInterface::class;

    /**
     * Either the keys must be configured or keyPath where oauth2-public.key, oauth2-private.key
     * and oauth2-encryption.key must exist
     *
     * @var string
     */
    public string $encryptionKey;
    public string $publicKey;
    public string $privateKey;
    public string $keyPath;

    /**
     * Interval when tokens expire, use interval notation
     * https://www.php.net/manual/en/dateinterval.construct.php
     *
     * @var string
     */
    public string|DateInterval $accessTokensExpireIn = 'PT6H';
    public string|DateInterval $refreshTokensExpireIn = 'P30D';

    /**
     * Map of 'identifier' => 'description'
     *
     * @var array
     */
    public array $scopes = [];

    private ServerRequest $_serverRequest;
    private ServerResponse $_serverResponse;
    public string $authorizationServerComponent = 'authorizationServer';
    public string $consoleControllerNamespace = 'JCIT\\oauth2\\commands';
    public string $defaultScope = '';
    public bool $enableImplicitGrant = false;
    public string $identityClass;
    public string $identityIdentifierColumn = 'identifier';
    public string|null $manageClientsRole = null;
    public array $urlManagerRules = [];
    public string $webControllerNamespace = 'JCIT\\oauth2\\controllers';

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = $this->consoleControllerNamespace;
        } elseif ($app instanceof \yii\web\Application) {
            $this->controllerNamespace = $this->webControllerNamespace;

            $app->getUrlManager()
                ->addRules(ArrayHelper::merge(
                    (new GroupUrlRule([
                    'ruleConfig' => [
                        'class' => UrlRule::class,
                        'pluralize' => false,
                        'only' => ['create', 'options']
                    ],
                    'rules' => ArrayHelper::merge([
                        ['controller' => [
                            $this->uniqueId . '/token',
                            $this->uniqueId . '/access-token' => $this->uniqueId . '/token'
                        ]],
                    ], $this->urlManagerRules)
                    ]))->rules,
                ['GET,POST ' . $this->uniqueId . '/authorize' => $this->uniqueId . '/authorize/authorize']
                ), true)
            ;
        }
    }

    protected function createAuthorizationServer(): AuthorizationServer
    {
        return new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $this->createPrivateCryptKey(),
            $this->createEncryptionKey(),
        );
    }

    protected function createEncryptionKey(): string|Key
    {
        $key = file_get_contents($this->keyPath . DIRECTORY_SEPARATOR . 'oauth2-encryption.key');
        return $this->encryptionKey ?? Key::loadFromAsciiSafeString($key);
    }

    protected function createPrivateCryptKey(): CryptKey
    {
        $key = $this->privateKey ?? 'file://' . $this->keyPath . DIRECTORY_SEPARATOR . 'oauth2-private.key';
        return new CryptKey($key, null, false);
    }

    public function createPublicCryptKey(): CryptKey
    {
        $key = $this->publicKey ?? 'file://' . $this->keyPath . DIRECTORY_SEPARATOR . 'oauth2-public.key';
        return new CryptKey($key, null, false);
    }

    public function getAuthorizationServer(): AuthorizationServer
    {
        return $this->get($this->authorizationServerComponent);
    }

    public function getDb(): Connection
    {
        return $this->get($this->db);
    }

    public function getServerRequest(): ServerRequest
    {
        if (!isset($this->_serverRequest)) {
            /** @var Request $request */
            $request = $this->module->get('request');
            $this->_serverRequest = (new ServerRequest($request))
                ->withParsedBody($request->bodyParams)
                ->withQueryParams($request->queryParams)
            ;
        }

        return $this->_serverRequest;
    }

    public function getServerResponse()
    {
        if (!isset($this->_serverResponse)) {
            $this->_serverResponse = new ServerResponse();
        }

        return $this->_serverResponse;
    }

    public function hasScope(string $identifier): bool
    {
        return $identifier === '*' || array_key_exists($identifier, $this->scopes);
    }

    public function init()
    {
        $this->accessTokenRepository = Instance::ensure($this->accessTokenRepository, AccessTokenRepositoryInterface::class);
        $this->authCodeRepository = Instance::ensure($this->authCodeRepository, AuthCodeRepositoryInterface::class);
        $this->clientRepository = Instance::ensure($this->clientRepository, ClientRepositoryInterface::class);
        $this->scopeRepository = Instance::ensure($this->scopeRepository, ScopeRepositoryInterface::class);
        $this->refreshTokenRepository = Instance::ensure($this->refreshTokenRepository, RefreshTokenRepositoryInterface::class);
        $this->userRepository = Instance::ensure($this->userRepository, UserRepositoryInterface::class);

        $this->keyPath = !empty($this->keyPath) ? \Yii::getAlias(rtrim($this->keyPath, DIRECTORY_SEPARATOR)) : $this->keyPath;

        /** Test if interval configuration is correct */
        $this->accessTokensExpireIn = $this->accessTokensExpireIn instanceof DateInterval ? $this->accessTokensExpireIn : new DateInterval($this->accessTokensExpireIn);
        $this->refreshTokensExpireIn = $this->refreshTokensExpireIn instanceof DateInterval ? $this->refreshTokensExpireIn : new DateInterval($this->refreshTokensExpireIn);

        if (!is_subclass_of($this->identityClass, UserEntityInterface::class)) {
            throw new InvalidConfigException('Identity class must implement ' . UserEntityInterface::class);
        }

        $this->registerTranslations();
        $this->registerAuthorizationServer();

        parent::init();
    }

    protected function registerAuthorizationServer(): void
    {
        $this->set($this->authorizationServerComponent, function () {
            $server = $this->createAuthorizationServer();
            $server->setDefaultScope($this->defaultScope);

            // Auth code grant
            $authCodeGrant = new AuthCodeGrant(
                $this->authCodeRepository,
                $this->refreshTokenRepository,
                new DateInterval('PT10M'),
            );
            $authCodeGrant->setRefreshTokenTTL($this->refreshTokensExpireIn);
            $server->enableGrantType($authCodeGrant, $this->accessTokensExpireIn);

            // Refresh token grant
            $refreshTokenGrant = new RefreshTokenGrant(
                $this->refreshTokenRepository
            );
            $refreshTokenGrant->setRefreshTokenTTL($this->refreshTokensExpireIn);
            $server->enableGrantType($refreshTokenGrant, $this->accessTokensExpireIn);

            // Password grant
            $passwordGrant = new PasswordGrant(
                $this->userRepository,
                $this->refreshTokenRepository
            );
            $passwordGrant->setRefreshTokenTTL($this->refreshTokensExpireIn);
            $server->enableGrantType($passwordGrant, $this->accessTokensExpireIn);

            // Client credentials grant
            $clientCredentialsGrant = new ClientCredentialsGrant();
            $server->enableGrantType($clientCredentialsGrant, $this->accessTokensExpireIn);

            // Implicit grant
            if ($this->enableImplicitGrant) {
                $implicitGrant = new ImplicitGrant($this->accessTokensExpireIn);
                $server->enableGrantType($implicitGrant, $this->accessTokensExpireIn);
            }

            return $server;
        });
    }

    protected function registerTranslations(): void
    {
        $this->module->i18n->translations['JCIT.oauth2'] =
            $this->module->i18n->translations['JCIT.oauth2']
            ?? [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@vendor/jc-it/yii2-oauth2-server/i18n'
            ];
    }
}
