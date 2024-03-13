<?php

use yii\helpers\Url;
use app\components\User;
use kartik\icons\Icon;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

Icon::map($this);

$this->title = Yii::t('app', 'Dashboard');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="body-content">
    <div class="row mt-2">
        <div class="col-lg-12 col-12">
            <div class="row mx-1">
                <?php if (User::can(40)) : ?>
                    <div class="col-lg-3 col-md-12">
                        <a href="<?= Url::to(['merchants/index']) ?>" class="small-box-footer">
                            <div class="info-box">
                                <span class="info-box-icon bg-info elevation-1">
                                    <div class="icon">
                                        <?= Icon::show('store') ?>
                                    </div>
                                </span>

                                <div class="info-box-content">
                                    <span class="info-box-text">
                                        <?= Yii::t('app', 'Totale Esercenti') ?>
                                    </span>
                                    <span class="info-box-number">
                                        <!-- <small>%</small> -->
                                    </span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (User::can(30)) : ?>
                    <div class="col-lg-3 col-md-12">
                        <a href="<?= Url::to(['stores/index']) ?>" class="small-box-footer">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1">
                                    <div class="icon">
                                        <?= Icon::show('shopping-cart') ?>
                                    </div>
                                </span>

                                <div class="info-box-content">
                                    <span class="info-box-text">
                                        <?= Yii::t('app', 'Totale Negozi') ?>
                                    </span>
                                    <span class="info-box-number">
                                        <!-- <small>%</small> -->
                                    </span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </a>
                    </div>
                <?php endif; ?>
                <div class="col-lg-3 col-md-12">
                    <a href="<?= Url::to(['pos/index']) ?>" class="small-box-footer">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary elevation-1">
                                <div class="icon">
                                    <?= Icon::show('mobile-alt') ?>
                                </div>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text">
                                    <?= Yii::t('app', 'Totale Pos') ?>
                                </span>
                                <span class="info-box-number">
                                    <!-- <small>%</small> -->
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </a>
                </div>

                <div class="col-lg-3 col-md-12">
                    <a href="<?= Url::to(['invoices/index']) ?>" class="small-box-footer">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning elevation-1">
                                <div class="icon">
                                    <?= Icon::show('file-invoice') ?>
                                </div>
                            </span>

                            <div class="info-box-content">
                                <span class="info-box-text">
                                    <?= Yii::t('app', 'Totale Transazioni') ?>
                                </span>
                                <span class="info-box-number">
                                    <!-- <small>%</small> -->
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-12 col-12">
            <div class="container-fluid">
                <div class="row">
                    <div class="<?php echo (User::can(40)) ? 'col-lg-6' : 'col-md-12'; ?>">
                        <?php //echo $this->render('dashboard/invoices', ['dataInvoices' => $dataInvoices]); ?>
                    </div>
                    <?php if (User::can(40)) : ?>
                        <div class="col-lg-6 col-md-12 ">
                            <?php //echo $this->render('dashboard/merchants', ['dataMerchants' => $dataMerchants]); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (User::can(30)) : ?>
                        <div class="col-lg-6 col-md-12 ">
                            <?php //echo $this->render('dashboard/stores', ['dataStores' => $dataStores]); ?>
                        </div>
                    <?php endif; ?>
                    <div class="col-lg-6 col-md-12 ">
                        <?php //echo $this->render('dashboard/pos', ['dataPos' => $dataPos]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>