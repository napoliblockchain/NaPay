<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Crypt;
use app\components\User;
use kartik\detail\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'condensed' => true,
    'hover' => true,
    'mode' => DetailView::MODE_VIEW,
    'enableEditMode' => FALSE,
    'panel' => [
        'heading' => Yii::t('app', 'Wallet'),
        'type' => DetailView::TYPE_INFO,
    ],
    'labelColOptions' => ['style' => 'width:40%'],
    'valueColOptions' => ['style' => 'width:60%'],
    'attributes' => [
        [
            'attribute' => 'derivationLabel',
            'format' => 'raw',
            'value' => Html::encode($model->storesettings->label),
        ],
        
        [
            'attribute' => 'derivationScheme',
            'format' => 'raw',
            'value' => Html::encode($model->storesettings->derivationScheme)
        ],
        [
            'attribute' => 'derivationAccountKeyPath',
            'format' => 'raw',
            'value' => Html::encode($model->storesettings->accountKeyPath)
        ],

       

    ],
]) ?>

<div class="card-footer">
    <div class="d-flex flex-row">
        <div class="p-1">
            <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Update'), ['update-wallet', 'id' => Crypt::encrypt($model->id)], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>