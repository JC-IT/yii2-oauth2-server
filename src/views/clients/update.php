<?php
declare(strict_types=1);

use JCIT\oauth2\models\form\clients\Update;
use kartik\password\PasswordInput;
use unclead\multipleinput\MultipleInput;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\web\View;

/**
 * @var Update $model
 * @var View $this
 */

$this->title = \Yii::t('JCIT.oauth2', 'Update {client}', ['client' => $model->getModel()->name]);

echo Html::tag('h1', $this->title);

$form = ActiveForm::begin([
    'layout' => ActiveForm::LAYOUT_HORIZONTAL,
    'method' => 'PUT',
]);

echo $form->field($model, 'name')->textInput();
echo $form->field($model, 'identifier')->textInput();
echo $form->field($model, 'newSecret')->widget(PasswordInput::class);
echo $form->field($model, 'newSecretConfirm')->passwordInput();
echo $form->field($model, 'redirectUris')->widget(MultipleInput::class);
echo $form->field($model, 'grantTypes')->checkboxList($model->grantOptions());
echo $form->field($model, 'allowedScopes')->checkboxList($model->scopeOptions());
echo $form->field($model, 'defaultScopes')->checkboxList($model->scopeOptions());

echo Html::beginTag('div', ['class' => ['text-right']]);
echo Html::a(\Yii::t('JCIT.oauth2', 'Cancel'), ['index'], ['class' => ['btn', 'btn-secondary']]);
echo Html::submitButton(\Yii::t('JCIT.oauth2', 'Save'), ['class' => ['btn', 'btn-primary', 'ml-3']]);
echo Html::endTag('div');

ActiveForm::end();
