<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\activeRecord;

use JCIT\oauth2\models\ActiveRecord;
use JCIT\oauth2\Module;
use JCIT\oauth2\queries\AccessTokenQuery;
use JCIT\oauth2\queries\ClientQuery;
use League\OAuth2\Server\Entities\UserEntityInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\validators\DefaultValueValidator;
use yii\validators\EachValidator;
use yii\validators\ExistValidator;
use yii\validators\RangeValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\validators\UniqueValidator;

/**
 * Class AccessToken
 * @package JCIT\oauth2\models\activeRecord
 *
 * @property int $id [int]
 * @property string $identifier [varchar(100)]
 * @property int $userId [int]
 * @property int $clientId [int]
 * @property string $name [varchar(255)]
 * @property array $scopes [json]
 * @property int $createdAt [timestamp]
 * @property int $updatedAt [timestamp]
 * @property int $expiresAt [timestamp]
 * @property int $revokedAt [timestamp]
 *
 * @property-read Client $client
 * @property-read bool $isRevoked
 * @property-read UserEntityInterface $user
 */
class AccessToken extends ActiveRecord
{
    protected string $clientClass = Client::class;

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

    public static function find(): AccessTokenQuery
    {
        return \Yii::createObject(AccessTokenQuery::class, [get_called_class()]);
    }

    public function getClient(): ClientQuery
    {
        return $this->hasOne($this->clientClass, ['id' => 'clientId']);
    }

    public function getIsRevoked(): bool
    {
        return !is_null($this->revokedAt);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(Module::getInstance()->identityClass, [Module::getInstance()->identityIdentifierColumn => 'userId']);
    }

    public function rules(): array
    {
        return [
            [['!clientId', '!identifier', '!userId'], RequiredValidator::class],
            [['!identifier'], StringValidator::class],
            [['!identifier'], UniqueValidator::class],
            [['scopes'], DefaultValueValidator::class, 'value' => []],
            [['scopes'], EachValidator::class, 'rule' => [RangeValidator::class, 'range' => array_keys($this->scopeOptions())]],
            [['name'], StringValidator::class],
            [['!clientId'], ExistValidator::class, 'targetRelation' => 'client'],
            [['!userId'], ExistValidator::class, 'targetRelation' => 'user'],
        ];
    }

    public function scopeOptions(): array
    {
        return Module::getInstance()->scopes;
    }
}
