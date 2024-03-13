<?php

use yii\helpers\Html;
use kartik\tabs\TabsX;
use app\components\User;

/* @var $this yii\web\View */
/* @var $model app\models\Stores */

$this->title = sprintf(Yii::t('app', 'Store ID: ') . '%s', $model->id);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Stores'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);


?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="stores-view">
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
                            <?php if (User::can(40)) : ?>
                                <div class="ml-auto">
                                    <?= Html::a('<button type="button" class="btn btn-warning">
                                        <i class="fas fa-plus"></i> ' . Yii::t('app', 'Nuovo negozio') . '
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
                                'content' => $this->render('_details-general', ['model' => $model, 'rates' => $rates]),
                                // 'visible' => !is_null($model),
                            ],
                            [
                                'label' => Yii::t('app','Tassi di cambio'),
                                'encode' => false,
                                'options' => ['id' => 'tab-rates'],
                                'content' => $this->render('_details-rates', ['model' => $model, 'rates' => $rates]),
                                'visible' => User::can(40),
                            ],
                            [
                                'label' => Yii::t('app', 'Ricevute'),
                                'encode' => false,
                                // 'active' => $modelSegnalazione->getIsAnnullata(),
                                'options' => ['id' => 'tab-checkout'],
                                'content' => $this->render('_details-checkout', ['model' => $model]),
                                'visible' => User::can(40),
                            ],
                            [
                                'label' => Yii::t('app', 'Criteri'),
                                'encode' => false,
                                // 'active' => $modelSegnalazione->getIsAnnullata(),
                                'options' => ['id' => 'tab-criteria'],
                                'content' => $this->render('_details-criteria', ['model' => $model]),
                                'visible' => User::can(40),
                            ],
                            [
                                'label' => Yii::t('app', 'Webhook'),
                                'encode' => false,
                                'options' => ['id' => 'tab-webhook'],
                                'content' => $this->render('_details-webhook', ['model' => $model]),
                                'visible' => User::can(40),
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
                </div>
            </div>
        </div>
    </div>
</div>