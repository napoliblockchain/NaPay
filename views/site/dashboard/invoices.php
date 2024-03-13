<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\Crypt;
use app\components\User;
?>
<div class="card card-outline card-primary">
    <div class="card-header border-0">
        <div class="card-title">
            <?= Html::a('<i class="fa fa-list"></i>', Url::to(['invoices/index']), [
                'title' => Yii::t('app', 'Lista'),
                'class' => 'btn btn-sm btn-default',
            ])  ?>
            <?= Yii::t('app', 'Transazioni') ?>
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
            'dataProvider' => $dataInvoices,
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

                            $url = Url::to(['invoices/view', 'id' => Crypt::encrypt($model->id)]);
                            return Html::a('<i class="fa fa-eye"></i>', $url, [
                                'title' => Yii::t('app', 'Dettagli'),
                                'class' => 'btn btn-sm btn-default btn-xl',
                            ]);
                        },

                    ],

                ],

                'invoiceId',

                [
                    'attribute' => 'createdTime',
                    // 'createdTime:datetime',
                    'value' => function ($data) {
                        return $data->createdTime;
                        // return Yii::$app->formatter->asDate(($data->createdTime), 'php:d/m/Y');
                    },
                    'format' => ['DateTime', 'php:H:i:s d/m/Y'],


                ],
                // 'destination',
                // [
                //     'attribute' => 'destination',
                //     'label' => Yii::t('app', 'Destination'),
                //     'format' => 'raw',
                //     'value' => function ($data) {
                //         $html = '';
                //         if (!empty($data->payments)) {
                //             foreach ($data->payments as $p) {
                //                 // $html .= '<p>'.$p->paymentMethod .': '. $p->destination.'</p>';
                //                 $html .= '<p class="single-line"><b>' . $p->paymentMethod . '</b>: ' . $p->destination . '</p>';
                //             }
                //         }
                //         return $html;
                //     },
                //     'contentOptions' => ['class' => 'text-break'],
                // ],

                
                'invoiceType',
                [
                    'attribute' => 'amount',
                    'value' => function ($data) {
                        return Yii::$app->formatter->asCurrency(($data->amount), $data->currency);
                    },
                ],
                'status',

                // [
                //     'attribute' => 'merchant_id',
                //     'format' => 'raw',
                //     'value' => function ($data) {
                //         return $data->merchant->description;
                //     },
                //     'visible' => User::can(40),
                // ],
                // 'store_id',
                // [
                //     'attribute' => 'store_id',
                //     'format' => 'raw',
                //     'value' => function ($data) {
                //         return $data->store->description;
                //     },
                //     'visible' => User::can(30),
                // ],

                // [
                //     'attribute' => 'pos_id',
                //     'format' => 'raw',
                //     'value' => function ($data) {
                //         return $data->pos->appName;
                //     },
                // ],
                // 'invoiceType',
                // 'status',
                // 'amount',
                // // 'currency',
                // 'createdTime:datetime',
               
            ],
        ]); ?>


    </div>
</div>