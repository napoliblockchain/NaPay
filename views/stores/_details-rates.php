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
        'heading' => Yii::t('app', 'Tassi di cambio'),
        'type' => DetailView::TYPE_INFO,
    ],
    'labelColOptions' => ['style' => 'width:40%'],
    'valueColOptions' => ['style' => 'width:60%'],
    'attributes' => [
        [
            'attribute' => 'preferredSource',
            'format' => 'raw',
            'value' => Html::encode($model->storesettings->preferredSource),
        ],
        
        [
            'attribute' => 'spread',
            'format' => 'raw',
            'value' => Html::encode($model->storesettings->spread)
        ],
        [
            'attribute' => 'Tasso per esercente',
            'value' => Html::encode(\Yii::$app->formatter->asCurrency((float) $rates)),
            'format' => 'raw',
        ],

        // importo_iniziale = 30750 / (1 - 5/100) = 32368.42€ (approssimato a due decimali)
        [
            'attribute' => 'Tasso originale senza spread',
            'value' => Html::encode(\Yii::$app->formatter->asCurrency((float) $rates / (1 - ($model->storesettings->spread / 100)))),
            'format' => 'raw',
        ],

    ],
]) ?>

<div class="card-footer">
    <div class="d-flex flex-row">
        <div class="p-1">
            <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Update'), ['update-rates', 'id' => Crypt::encrypt($model->id)], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>