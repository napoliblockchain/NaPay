<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stores */
/* @var $form yii\bootstrap4\ActiveForm */


$webhook = $model->webhook;

$newUrl = Url::to(['callback/index'], true);
?>

<div class="stores-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($webhook, 'webhookId')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>

    <p class="text-primary">
        Url attuale: <?= Html::encode($webhook->url) ?>
    </p>

    <?= $form->field($webhook, 'url')->textInput(['maxlength' => true, 'value'=>$newUrl])->label('Url suggerito') ?>


    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Conferma'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>