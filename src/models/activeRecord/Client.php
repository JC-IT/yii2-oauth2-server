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
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\validators\UniqueValidator;
use yii\validators\UrlValidator;

/**
 * Class Client
 * @package JCIT\oauth2\models\activeRecord
 *
 * @property int $id [int]
 * @property string $identifier [varchar(100)]
 * @property string $name [varchar(255)]
 * @property string $secret [varchar(255)]
 * @property array $redirectUris [json]
 * @property array $grantTypes [json]
 * @property array $scopes [json]
 * @property bool $passwordClient [tinyint(1)]
 * @property int $createdAt [timestamp]
 * @property int $updatedAt [timestamp]
 * @property int $revokedAt [timestamp]
 *
 * @property-read bool $isConfidential
 * @property-read bool $isRevoked
 */
class Client extends ActiveRecord
{
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
            'authorization_code' => \Yii::t('oauth2', 'Authorization code'),
            'client_credentials' => \Yii::t('oauth2', 'Client credentials'),
            'implicit' => \Yii::t('oauth2', 'Implicit'),
            'password' => \Yii::t('oauth2', 'Password'),
            'refresh_token' => \Yii::t('oauth2', 'Refresh token'),
        ];
    }

    public function rules(): array
    {
        return [
            [['identifier', 'grantTypes', 'name'], RequiredValidator::class],
            [['identifier'], StringValidator::class],
            [['identifier'], UniqueValidator::class],
            [['name'], StringValidator::class],
            [['secret'], StringValidator::class, 'min' => 8],
            [['grantTypes', 'redirectUris', 'scopes'], DefaultValueValidator::class, 'value' => []],
            [['scopes'], EachValidator::class, 'rule' => [RangeValidator::class, 'range' => array_keys($this->scopeOptions())]],
            [['redirectUris'], EachValidator::class, 'rule' => [UrlValidator::class]],
            [['grantTypes'], EachValidator::class, 'rules' => [RangeValidator::class, 'range' => array_keys($this->grantOptions())]],
            [['passwordClient'], BooleanValidator::class],
        ];
    }

    public function scopeOptions(): array
    {
        return Module::getInstance()->scopes;
    }
}
