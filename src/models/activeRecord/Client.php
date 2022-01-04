<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\activeRecord;

use JCIT\oauth2\models\ActiveRecord;
use JCIT\oauth2\Module;
use JCIT\oauth2\queries\ClientQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\validators\BooleanValidator;
use yii\validators\DefaultValueValidator;
use yii\validators\EachValidator;
use yii\validators\RangeValidator;
use yii\validators\RegularExpressionValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\validators\UniqueValidator;
use yii\validators\UrlValidator;

/**
 * @property int $id [int]
 * @property string $identifier [varchar(100)]
 * @property string $name [varchar(255)]
 * @property string $secret [varchar(255)]
 * @property array $redirectUris [json]
 * @property array $grantTypes [json]
 * @property array $allowedScopes [json]
 * @property array $defaultScopes [json]
 * @property int $createdAt [timestamp]
 * @property int $updatedAt [timestamp]
 * @property int $revokedAt [timestamp]
 *
 * @property-read bool $isConfidential
 * @property-read bool $isRevoked
 */
class Client extends ActiveRecord
{
    public function attributeHints(): array
    {
        return [
            'allowedScopes' => \Yii::t('JCIT.oauth2', 'The client is only allowed to use these scopes.'),
            'defaultScopes' => \Yii::t('JCIT.oauth2', 'These scopes will automatically be requested.'),
            'grantTypes' => \Yii::t('JCIT.oauth2', 'The login methods this client is allowed to use.'),
            'identifier' => \Yii::t('JCIT.oauth2', 'The username the client required to identify itself. Can only contain a-z, A-Z, 0-9 and -.'),
            'redirectUris' => \Yii::t('JCIT.oauth2', 'URLs that can be redirected to. These need to be full urls (except query parameters)!'),
            'secret' => \Yii::t('JCIT.oauth2', 'The password the client required to identify itself.'),
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'allowedScopes' => \Yii::t('JCIT.oauth2', 'Allowed scopes'),
            'defaultScopes' => \Yii::t('JCIT.oauth2', 'Default scopes'),
            'grantTypes' => \Yii::t('JCIT.oauth2', 'Allowed grants'),
            'identifier' => \Yii::t('JCIT.oauth2', 'Client username'),
            'name' => \Yii::t('JCIT.oauth2', 'Name'),
            'redirectUris' => \Yii::t('JCIT.oauth2', 'Redirect URLs'),
            'secret' => \Yii::t('JCIT.oauth2', 'Client password'),
        ];
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                TimestampBehavior::class => [
                    'class' => TimestampBehavior::class,
                    'createdAtAttribute' => 'createdAt',
                    'updatedAtAttribute' => 'updatedAt',
                    'value' => new Expression('NOW()'),
                ]
            ]
        );
    }

    public static function find(): ClientQuery
    {
        return \Yii::createObject(ClientQuery::class, [get_called_class()]);
    }

    public function getIsConfidential(): bool
    {
        return !is_null($this->secret);
    }

    public function getIsRevoked(): bool
    {
        return !is_null($this->revokedAt);
    }

    public function grantOptions(): array
    {
        return [
            'authorization_code' => \Yii::t('JCIT.oauth2', 'Authorization code'),
            'client_credentials' => \Yii::t('JCIT.oauth2', 'Client credentials'),
            'implicit' => \Yii::t('JCIT.oauth2', 'Implicit'),
            'password' => \Yii::t('JCIT.oauth2', 'Password'),
            'refresh_token' => \Yii::t('JCIT.oauth2', 'Refresh token'),
        ];
    }

    public function rules(): array
    {
        return [
            [['identifier', 'grantTypes', 'name'], RequiredValidator::class],
            [['identifier'], StringValidator::class, 'min' => 12],
            [['identifier'], RegularExpressionValidator::class, 'pattern' => '/^[a-z0-9\-]{12,}$/'],
            [['identifier'], UniqueValidator::class],
            [['name'], StringValidator::class],
            [['secret'], StringValidator::class, 'min' => 8],
            [['allowedScopes', 'defaultScopes', 'grantTypes', 'redirectUris'], DefaultValueValidator::class, 'value' => []],
            [['allowedScopes', 'defaultScopes'], EachValidator::class, 'rule' => [RangeValidator::class, 'range' => array_keys($this->scopeOptions())]],
            [['redirectUris'], EachValidator::class, 'rule' => [UrlValidator::class]],
            [['grantTypes'], EachValidator::class, 'rule' => [RangeValidator::class, 'range' => array_keys($this->grantOptions())]],
        ];
    }

    public function scopeOptions(): array
    {
        return ArrayHelper::merge(['*' => \Yii::t('JCIT.oauth2', 'All scopes')], Module::getInstance()->scopes);
    }
}
