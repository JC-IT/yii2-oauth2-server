<?php
declare(strict_types=1);

use JCIT\oauth2\models\Authorize;
use JCIT\oauth2\objects\Scope;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\web\View;

/**
 * @var string $authToken
 * @var Authorize $model
 * @var Scope[] $scopes
 * @var View $this
 */

$this->title = \Yii::t('JCIT.oauth2', 'Authorize {client}', ['client' => $model->getClient()->getName()]);

echo Html::tag('h1', $this->title);

$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'method' => 'post',
]);

echo $form->field($model, 'client')->staticControl(['value' => $model->getClient()->getName()]);

echo Html::beginTag('div', ['class' => ['form-group', 'row', 'field-authorize-scopes']]);
echo Html::label($model->getAttributeLabel('scopes'), null, ['class' => ['col-sm-2', 'col-form-label']]);
echo Html::tag('div', Html::ul(array_map(static fn(Scope $scope) => $scope->getDescription(),$scopes), ['style' => ['padding-left' => '1.5rem']]), ['class' => ['col-10', 'form-control-plaintext']]);
echo Html::endTag('div');

echo Html::beginTag('div', ['class' => ['text-right']]);
echo Html::submitButton(\Yii::t('JCIT.oauth2', 'Reject'), ['name' => Html::getInputName($model, 'accept'), 'value' => 0, 'class' => ['btn', 'btn-secondary']]);
echo Html::submitButton(\Yii::t('JCIT.oauth2', 'Authorize'), ['name' => Html::getInputName($model, 'accept'), 'value' => 1, 'class' => ['btn', 'btn-primary', 'ml-3']]);
echo Html::endTag('div');

ActiveForm::end();
