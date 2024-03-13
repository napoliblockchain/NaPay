<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\Crypt;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\SettingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Impostazioni');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="settings-index">
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
                            <h3><?= Html::encode($this->title) ?></h3>
                            <div class="ml-auto">
                                <?= Html::a('<button type="button" class="btn btn-warning">
                                    <i class="fas fa-plus"></i> ' . Yii::t('app', 'Aggiungi') . '
                                    </button>', ['create']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    // ['class' => 'yii\grid\SerialColumn'],

                                    // 'id',
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
                                                    'title' => Yii::t('app', 'Vista'),
                                                    'class' => 'btn btn-sm btn-default',
                                                ]);
                                            },

                                        ],

                                    ],
                                    'description',
                                    'code',
                                    [
                                        'attribute' => 'value',
                                        'format' => 'raw',
                                        'value' => function ($data) {
                                            return Html::encode($data->value);
                                        },
                                        'contentOptions' => ['class' => 'text-break'],
                                    ],

                                    // ['class' => 'yii\grid\ActionColumn'],
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>