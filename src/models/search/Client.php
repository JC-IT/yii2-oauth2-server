<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\search;

use JCIT\models\search\ActiveSearch;
use JCIT\oauth2\models\activeRecord\Client as ActiveRecordClient;
use yii\db\ActiveQuery;
use yii\validators\StringValidator;

class Client extends ActiveSearch
{
    protected string $baseModelClass = ActiveRecordClient::class;

    public string|null $name = null;

    protected function internalSearchQuery(ActiveQuery $query): void
    {
        $query->andFilterWhere(['name' => $this->name]);
    }

    public function rules(): array
    {
        return [
            [['name'], StringValidator::class],
        ];
    }
}
