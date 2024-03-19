<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\detail\DetailView;

use app\components\User;
use app\components\Crypt;

?>

<?= DetailView::widget([
    'model' => $model,
    'condensed' => true,
    'hover' => true,
    'mode' => DetailView::MODE_VIEW,
    'enableEditMode' => FALSE,
    'panel' => [
        // 'heading' => Yii::t('app','Overview'),
        'type' => DetailView::TYPE_INFO,
    ],
    'labelColOptions' => ['style' => 'width:30%'],
    'valueColOptions' => ['style' => 'width:70%'],
    'attributes' => [
        [
            'attribute' => 'username',
            'value' => Html::encode($model->username),
            'format' => 'raw',
        ],
        [
            'attribute' => 'first_name',
            'value' => Html::encode($model->first_name . chr(32) . $model->last_name),
            'format' => 'raw',
        ],
        [
            'attribute' => 'email',
            'value' => Html::encode($model->email),
            'format' => 'raw',
        ],

        // [
        //     'attribute' => 'merchant_id',
        //     'value' => Html::encode($model->merchant->description ?? null),
        //     'format' => 'raw',
        //     'visible' => (User::privilegeLevel($model->id) < 40) ? true : false,
        // ],

        // [
        //     'attribute' => 'store_id',
        //     'value' => Html::encode($model->store->description ?? null),
        //     'format' => 'raw',
        //     'visible' => (User::privilegeLevel($model->id) < 40) ? true : false,
        // ],

        [
            'attribute' => 'privilege_id',
            'value' => Html::encode($model->privilege->description),
            'format' => 'raw',
        ],

    ],
]) ?>

<?php if (User::isMerchant()):  ?>
    <div class="row">
        <div class="col-3">
            <?= Html::a('<i class="fa fa-eye"></i> ' . Yii::t('app','Merchant details'), Url::to(['merchants/view', 'id' => Crypt::encrypt(User::getMerchantId())]), [
                'title' => Yii::t('app', 'Dettaglio'),
                'class' => 'btn btn-primary',
            ]) ; ?>
        </div>
    </div>
<?php endif; ?>
   