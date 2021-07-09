<?php
declare(strict_types=1);

use JCIT\oauth2\models\search\Client;
use yii\bootstrap4\Html;
use yii\data\DataProviderInterface;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\web\View;

/**
 * @var DataProviderInterface $dataProvider
 * @var Client $searchModel
 * @var View $this
 */

$this->title = \Yii::t('JCIT.oauth2', 'Manage OAuth clients');

echo Html::tag('h1', $this->title);

echo Html::beginTag('div', ['class' => ['text-right']]);
echo Html::a(\Yii::t('app', 'Create client'), ['create'], ['class' => ['btn', 'btn-primary']]);
echo Html::endTag('div');

echo GridView::widget([
    'filterModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'columns' => [
        'name',
        [
            'class' => ActionColumn::class,
            'template' => '{update} {delete}',
        ]
    ]
]);
