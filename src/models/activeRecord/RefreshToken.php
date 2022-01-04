<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\activeRecord;

use JCIT\oauth2\models\ActiveRecord;
use JCIT\oauth2\queries\AccessTokenQuery;
use JCIT\oauth2\queries\RefreshTokenQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\validators\ExistValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\validators\UniqueValidator;

/**
 * @property int $id [int]
 * @property string $identifier [varchar(100)]
 * @property int $accessTokenId [int]
 * @property int $createdAt [timestamp]
 * @property int $updatedAt [timestamp]
 * @property int $expiresAt [timestamp]
 * @property int $revokedAt [timestamp]
 *
 * @property-read AccessToken $accessToken
 * @property-read bool $isRevoked
 */
class RefreshToken extends ActiveRecord
{
    protected string $accessTokenClass = AccessToken::class;

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

    public static function find(): RefreshTokenQuery
    {
        return \Yii::createObject(RefreshTokenQuery::class, [get_called_class()]);
    }

    public function getAccessToken(): AccessTokenQuery
    {
        return $this->hasOne($this->accessTokenClass, ['id' => 'accessTokenId']);
    }

    public function getIsRevoked(): bool
    {
        return !is_null($this->revokedAt);
    }

    public function rules(): array
    {
        return [
            [['!accessTokenId', '!identifier'], RequiredValidator::class],
            [['!identifier'], StringValidator::class],
            [['!identifier'], UniqueValidator::class],
            [['!accessTokenId'], ExistValidator::class, 'targetRelation' => 'accessToken'],
        ];
    }
}
