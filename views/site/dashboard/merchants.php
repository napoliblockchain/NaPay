<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\Crypt;
?>
<div class="card card-outline card-primary">
    <div class="card-header border-0">
        <div class="card-title">
            <?= Html::a('<i class="fa fa-list"></i>', Url::to(['merchants/index']), [
                'title' => Yii::t('app', 'Lista'),
                'class' => 'btn btn-sm btn-default',
            ])  ?>
            <?= Yii::t('app', 'Esercenti') ?>
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
            'dataProvider' => $dataMerchants,
            'tableOptions' => [
                'class' => 'table table-sm  table-valign-middle small',
            ],
            'summary' => false,
            'columns' => [
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'contentOptions' => [
                        'style' => 'width: 35px;'
                    ],
                    'buttons' => [
                        'view' => function ($url, $model) {

                            $url = Url::to(['merchants/view', 'id' => Crypt::encrypt($model->id)]);
                            return Html::a('<i class="fa fa-eye"></i>', $url, [
                                'title' => Yii::t('app', 'Dettagli'),
                                'class' => 'btn btn-sm btn-default btn-xl',
                            ]);
                        },

                    ],

                ],

                // 'id'

                'description',
                'vatNumber',
                'email:email',

            ],
        ]); ?>


    </div>
</div>