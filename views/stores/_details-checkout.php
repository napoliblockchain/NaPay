<?php

use yii\helpers\Html;
use app\components\Crypt;
use kartik\detail\DetailView;
use app\components\HtmlStore;


$select = ['No','Si'];
    
?>

<?= DetailView::widget([
    'model' => $model,
    'condensed' => true,
    'hover' => true,
    'mode' => DetailView::MODE_VIEW,
    'enableEditMode' => FALSE,
    'panel' => [
        'heading' => Yii::t('app', 'Aspetto della ricevuta di pagamento'),
        'type' => DetailView::TYPE_INFO,
    ],
    'labelColOptions' => ['style' => 'width:15%'],
    'valueColOptions' => ['style' => 'width:35%'],
    'attributes' => [
       
        [
            'attribute' => 'requiresRefundEmail',
            'format' => 'raw',
            'value' => Html::encode($select[$model->storesettings->requiresRefundEmail])
        ],
        
        [
            'attribute' => 'receipt',
            'value' => HtmlStore::receipt($model->storesettings->receipt),
            'format' => 'raw',
        ],

    ],
]) ?>



<div class="card-footer">
    <div class="d-flex flex-row">
        <div class="p-1">
            <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Modifica'), ['update-checkout', 'id' => Crypt::encrypt($model->id)], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>