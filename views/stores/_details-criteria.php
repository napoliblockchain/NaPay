<?php

use yii\helpers\Html;
use app\components\Crypt;
use kartik\detail\DetailView;
use app\components\HtmlStore;

?>

<?= DetailView::widget([
    'model' => $model,
    'condensed' => true,
    'hover' => true,
    'mode' => DetailView::MODE_VIEW,
    'enableEditMode' => FALSE,
    'panel' => [
        'heading' => Yii::t('app', 'Criteri di pagamento'),
        'type' => DetailView::TYPE_INFO,
    ],
    'labelColOptions' => ['style' => 'width:15%'],
    'valueColOptions' => ['style' => 'width:35%'],
    'attributes' => [

        [
            'attribute' => 'paymentMethodCriteria',
            'format' => 'raw',
            'value' => HtmlStore::paymentMethodCriteria($model->storesettings->paymentMethodCriteria)
        ],


    ],
]) ?>

<div class="card-body card-outline card-primary">
    <p class="text-info">Stabilisci le soglie per l'utilizzo di Bitcoin e/o Lightning network. Il Lightning Network offre transazioni istantanee e meno costose rispetto a Bitcoin.</p>
    <p class="text-info">Il valore per questo campo, di norma, può essere quello di 10 €</p>
    <p class="text-info">Ricorda, l'uso di Lightning Network è ideale per piccole transazioni, mentre per importi maggiori è preferibile utilizzare Bitcoin.</p>
</div>

<div class="card-footer">
    <div class="d-flex flex-row">
        <div class="p-1">
            <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Modifica'), ['update-criteria', 'id' => Crypt::encrypt($model->id)], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>