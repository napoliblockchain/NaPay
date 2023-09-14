<?php

use yii\helpers\Html;
use app\components\Crypt;
use kartik\detail\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'condensed' => true,
    'hover' => true,
    'mode' => DetailView::MODE_VIEW,
    'enableEditMode' => FALSE,
    'panel' => [
        'heading' => Yii::t('app', 'Webhook'),
        'type' => DetailView::TYPE_INFO,
    ],
    'labelColOptions' => ['style' => 'width:15%'],
    'valueColOptions' => ['style' => 'width:35%'],
    'attributes' => [
       
        [
            'attribute' => 'url',
            'format' => 'raw',
            'value' => Html::encode($model->webhook->url ?? null)
        ],
        [
            'attribute' => 'webhook_Id',
            'format' => 'raw',
            'value' => Html::encode($model->webhook->webhookId ?? null)
        ],
        

    ],
]) ?>

<div class="card-footer">
    <div class="d-flex flex-row">
        <div class="p-1">
            <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Modifica'), ['update-webhook', 'id' => Crypt::encrypt($model->id)], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>