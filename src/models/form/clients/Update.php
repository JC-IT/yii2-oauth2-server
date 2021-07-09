<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\form\clients;

use kartik\password\StrengthValidator;
use yii\base\Security;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\CompareValidator;
use yii\validators\DefaultValueValidator;
use yii\validators\EachValidator;
use yii\validators\RangeValidator;
use yii\validators\RegularExpressionValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\validators\UniqueValidator;
use yii\validators\UrlValidator;

class Update extends ActiveForm
{
    public array|string|null $allowedScopes = [];
    public array|string|null $defaultScopes = [];
    public array|string|null $grantTypes = [];
    public string|null $identifier = null;
    public string|null $name = null;
    public string|null $newSecret = null;
    public string|null $newSecretConfirm = null;
    public array|string|null $redirectUris = [];

    public function __construct(
        ActiveRecord $model,
        protected Security $security,
        $config = []
    ) {
        parent::__construct($model, $config);
    }

    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'newSecret' => \Yii::t('JCIT.oauth2', 'New client password'),
            'newSecretConfirm' => \Yii::t('JCIT.oauth2', 'Confirm new client password'),
        ]);
    }

    public function grantOptions(): array
    {
        return $this->model->grantOptions();
    }

    public function rules(): array
    {
        return [
            [['allowedScopes', 'defaultScopes', 'grantTypes', 'identifier', 'name', 'redirectUris'], RequiredValidator::class],
            [['allowedScopes', 'defaultScopes', 'grantTypes', 'redirectUris'], DefaultValueValidator::class, 'value' => []],
            [['allowedScopes', 'defaultScopes'], EachValidator::class, 'rule' => [RangeValidator::class, 'range' => array_keys($this->scopeOptions())]],
            [['grantTypes'], EachValidator::class, 'rule' => [RangeValidator::class, 'range' => array_keys($this->grantOptions())]],
            [['identifier'], StringValidator::class, 'min' => 12],
            [['identifier'], RegularExpressionValidator::class, 'pattern' => '/^[a-z0-9\-]{12,}$/'],
            [['identifier'], UniqueValidator::class, 'targetClass' => get_class($this->model), 'filter' => function(ActiveQuery $query){$query->andWhere(['not', ['id' => $this->getModel()->id]]);}],
            [['name'], StringValidator::class, 'min' => 12],
            [['newSecret'], StrengthValidator::class, 'min' => 10, 'upper' => 1, 'lower' => 1, 'digit' => 1, 'special' => 1, 'hasUser' => false, 'hasEmail' => false],
            [['newSecretConfirm'], CompareValidator::class, 'compareAttribute' => 'newSecret'],
            [['redirectUris'], EachValidator::class, 'rule' => [UrlValidator::class]],
        ];
    }

    protected function runInternalModel(): bool
    {
        $data = $this->getDataAttributes();
        if (in_array('*', $data['allowedScopes'] ?? [])) {
            $data['allowedScopes'] = ['*'];
        }

        if (in_array('*', $data['defaultScopes'] ?? [])) {
            $data['defaultScopes'] = ['*'];
        }

        if (!empty($data['newSecret'])) {
            $data['secret'] = $this->security->generatePasswordHash($data['newSecret']);
        }

        $this->model->setAttributes($data, $this->safeOnly);
        return $this->model->save();
    }

    public function scopeOptions(): array
    {
        return $this->model->scopeOptions();
    }
}
