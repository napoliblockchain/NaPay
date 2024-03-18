<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\Crypt;
use app\components\User;
use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model app\models\Commercianti */

$this->title = sprintf('Dettaglio Esercente - ID: %s', $model->id);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Esercenti'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="commercianti-view">
                <?= app\widgets\Alert::widget() ?>
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-row">
                            <h3><?= Html::encode($this->title) ?></h3>
                            <?php if (User::can(30)) : ?>
                                <div class="ml-auto">
                                    <?= Html::a('<button type="button" class="btn btn-warning">
                                        <i class="fas fa-plus"></i> ' . Yii::t('app', 'Nuovo esercente') . '
                                        </button>', ['create']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        $items = [
                            [
                                'label' => Yii::t('app', 'Generale'),
                                'encode' => false,
                                // 'active' => !$modelSegnalazione->getIsAnnullata(),
                                'options' => ['id' => 'tab-general'],
                                'content' => $this->render('_details-general', ['model' => $model]),
                                // 'visible' => !is_null($model),
                            ],
                        ];
                        echo TabsX::widget([
                            'items' => $items,
                            'bordered' => true,
                            'position' => TabsX::POS_ABOVE,
                            'encodeLabels' => false,
                            'enableStickyTabs' => true,
                        ]);

                        ?>

                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-row">
                            <div>
                                <?= Html::a('<i class="fas fa-pen"></i> ' . Yii::t('app', 'Modifica'), ['update', 'id' => Crypt::encrypt($model->id)], ['class' => 'btn btn-primary']) ?>
                            </div>
                            <?php if (User::isAdministrator()) : ?>
                                <div class="ml-auto">
                                    <?= Html::a('<i class="fas fa-trash"></i> ' . Yii::t('app', 'Elimina'), ['delete', 'id' => Crypt::encrypt($model->id)], [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('app', 'Vuoi eliminare questo esercente?'),
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>