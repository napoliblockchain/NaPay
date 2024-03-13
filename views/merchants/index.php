<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\Crypt;
use app\components\User;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CommerciantiSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Esercenti');
$this->params['breadcrumbs'][] = $this->title;

$status_list = ['Attivo', 'Disabilitato'];
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="merchants-index">
                <?php if (Yii::$app->session->hasFlash('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo Yii::$app->session->getFlash('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (Yii::$app->session->hasFlash('error')) : ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?php echo Yii::$app->session->getFlash('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-row">
                            <h3 class="p-1"><?= Html::encode($this->title) ?></h3>

                            <?php if (User::can(30)) : ?>
                                <div class="ml-auto p-1">
                                    <?= Html::a('<button type="button" class="btn btn-warning">
                                        <i class="fas fa-plus"></i> ' . Yii::t('app', 'Nuovo esercente') . '
                                        </button>', ['create']) ?>
                                </div>
                                <div class="p-1">
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'export-form',
                                        'action' => ['export'],
                                        'method' => 'post',
                                    ]); ?>
                                    <input type='hidden' name='queryParams' value='<?= $queryParams ?>' />

                                    <?= Html::submitButton('<i class="fas fa-file-excel mr-1"></i>' . Yii::t('app', 'Esporta'), [
                                        'class' => 'btn btn-success',
                                        'target' => '_blank'
                                    ]) ?>
                                    <?php ActiveForm::end(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'columns' => [
                                // ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'id',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return $data->id;
                                    },
                                    'contentOptions' => [
                                        'style' => 'width: 60px;',
                                        'class' => 'text-center',
                                    ],
                                ],

                                // view button
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view}',
                                    'contentOptions' => [
                                        'style' => 'width: 35px;'
                                    ],
                                    'buttons' => [
                                        'view' => function ($url, $model) {

                                            $url = Url::to(['view', 'id' => Crypt::encrypt($model->id)]);
                                            return Html::a('<i class="fa fa-eye"></i>', $url, [
                                                'title' => Yii::t('app', 'Dettaglio'),
                                                'class' => 'btn btn-sm btn-default',
                                            ]);
                                        },

                                    ],

                                ],

                                // 'id',
                                'description',
                                'vatNumber',
                                'email:email',
                                //'create_date',
                                //'close_date',
                                //'historical',
                                // [
                                //     'attribute' => 'historical',
                                //     'label' => 'Stato',
                                //     'value' => function($data) use ($status_list){
                                //         return $status_list[$data->historical];
                                //     },
                                //     'filter' => $status_list

                                // ],

                                // ['class' => 'yii\grid\ActionColumn'],

                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>