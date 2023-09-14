<?php

use yii\helpers\Html;
use app\components\Crypt;

/* @var $this yii\web\View */
/* @var $model app\models\Invoices */

$this->title = Yii::t('app', 'Transazione: {name}', [
    'name' => $model->invoiceId,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transazioni'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoiceId, 'url' => ['view', 'id' => Crypt::encrypt($model->id)]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Modifica');
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="invoices-update">
                <?php if (Yii::$app->session->hasFlash('error')) : ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?php echo Yii::$app->session->getFlash('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h5><?= Html::encode($this->title) ?></h5>
                    </div>
                    <div class="card-body">
                        <?= $this->render('_form', [
                            'model' => $model,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>