<?php
declare(strict_types=1);

namespace JCIT\oauth2\models\form\clients;

use yii\helpers\ArrayHelper;

class Create extends Update
{
    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'newSecret' => \Yii::t('JCIT.oauth2', 'Client password'),
            'newSecretConfirm' => \Yii::t('JCIT.oauth2', 'Confirm client password'),
        ]);
    }
}
