<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stores */
/* @var $form yii\bootstrap4\ActiveForm */


$store = $model->storesettings;

$select = ['No', 'Si'];

$receipt = json_decode($store->receipt);

$store->receipt_enabled = $receipt->enabled;
$store->receipt_showPayments = $receipt->showPayments;
$store->receipt_showQR = $receipt->showQR;


?>

<div class="stores-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= Html::label($store->getAttributeLabel('requiresRefundEmail')) ?>
    <?= $form->field($store, 'requiresRefundEmail', ['options' => ['class' => 'w-25']])
        ->label(false)
        ->dropDownList($select) ?>


    <?= Html::label($store->getAttributeLabel('receipt_enabled'), null, ['class' => 'mt-2']) ?>
    <?= $form->field($store, 'receipt_enabled', ['options' => ['class' => 'w-25']])
        ->label(false)
        ->dropDownList($select) ?>


    <?= Html::label($store->getAttributeLabel('receipt_showPayments'), null, ['class' => 'mt-2']) ?>
    <?= $form->field($store, 'receipt_showPayments', ['options' => ['class' => 'w-25']])
        ->label(false)
        ->dropDownList($select) ?>

    <?= Html::label($store->getAttributeLabel('receipt_showQR'), null, ['class' => 'mt-2']) ?>
    <?= $form->field($store, 'receipt_showQR', ['options' => ['class' => 'w-25']])
        ->label(false)
        ->dropDownList($select) ?>

    <div class="form-group mt-4">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Salva'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>