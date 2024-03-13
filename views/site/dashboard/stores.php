<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\Crypt;
use app\components\User;

$speedPolicy = [
    'HighSpeed' => Yii::t('app', 'High Speed: 0 confirmations'),
    'MediumSpeed' => Yii::t('app', 'Medium Speed: 1 confirmations'),
    'LowMediumSpeed' => Yii::t('app', 'Low Medium Speed: 2 confirmations'),
    'LowSpeed' => Yii::t('app', 'Low Speed: 6 confirmations'),

];
?>
<div class="card card-outline card-primary">
    <div class="card-header border-0">
        <div class="card-title">
            <?= Html::a('<i class="fa fa-list"></i>', Url::to(['stores/index']), [
                'title' => Yii::t('app', 'Lista'),
                'class' => 'btn btn-sm btn-default',
            ])  ?>
            <?= Yii::t('app', 'Negozi') ?>
        </div>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body table-responsive p-1">
        <?= GridView::widget([
            'dataProvider' => $dataStores,
            'tableOptions' => [
                'class' => 'table table-sm table-valign-middle small',
            ],
            'summary' => false,
            'columns' => [
                // ['class' => 'yii\grid\SerialColumn'],

                // view button
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'contentOptions' => [
                        'style' => 'width: 35px;'
                    ],
                    'buttons' => [
                        'view' => function ($url, $model) {

                            $url = Url::to(['stores/view', 'id' => Crypt::encrypt($model->id)]);
                            return Html::a('<i class="fa fa-eye"></i>', $url, [
                                'title' => Yii::t('app', 'Dettagli'),
                                'class' => 'btn btn-sm btn-default btn-xl',
                            ]);
                        },

                    ],

                ],

                [
                    'attribute' => 'merchant_id',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return $data->merchant->description ?? null;
                    },
                    'visible' => User::can(40),
                ],
                'description',
                // [
                //     'attribute' => 'storesettings.bps_storeid',
                //     'format' => 'raw',
                //     'value' => 'storesettings.bps_storeid',
                //     // 'value' => function ($data) {
                //     //     return $data->storesettings->bps_storeid;
                //     // },
                //     'contentOptions' => ['class' => 'text-break'],
                // ],

                [
                    'attribute' => 'storesettings.speedPolicy',
                    'value' => function ($data) use ($speedPolicy) {
                        return $speedPolicy[$data->storesettings->speedPolicy];
                    },
                    // 'contentOptions' => ['class' => 'text-break'],
                ],





            ],
        ]); ?>

    </div>
</div>