<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stores */
/* @var $form yii\bootstrap4\ActiveForm */


$store = $model->storesettings;

$networkFeeMode = [
    'Always' => Yii::t('app', 'Always'),
    'MultiplePaymentsOnly' => Yii::t('app', 'Multiple Payments Only'),
    'Never' => Yii::t('app', 'Never'),
];
$speedPolicy = [
    'HighSpeed' => Yii::t('app', 'High Speed: 0 confirmations'),
    'MediumSpeed' => Yii::t('app', 'Medium Speed: 1 confirmations'),
    'LowMediumSpeed' => Yii::t('app', 'Low Medium Speed: 2 confirmations'),
    'LowSpeed' => Yii::t('app', 'Low Speed: 6 confirmations'),

];

$defaultPaymentMethod = ['BTC'=>'BTC'];
$defaultCurrency = ['EUR'=>'EUR','USD'=>'USD'];

?>

<div class="stores-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'merchant_id')->dropDownList($merchants_list, ['disabled' => 'disabled']) ?>
    <?= $form->field($model, 'description')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>
    
    
    <?= $form->field($store, 'speedPolicy')->dropDownList($speedPolicy) ?>
    <p class="text-info">
        Seleziona il numero di conferme sulla Blockchain necessarie per considerare una transazione su rete Bitcoin completata. Un numero più alto di conferme offre una maggiore sicurezza sul pagamento, azzerando la probabilità da parte di un malintenzionato di effettuare un doppio pagamento o altre forme di attacco. Tuttavia, ricorda che un numero più alto di conferme comporta tempi di attesa più lunghi per la finalizzazione della transazione.
    </p>

    <?= $form->field($store, 'networkFeeMode')->dropDownList($networkFeeMode) ?>

    <?= $form->field($store, 'invoiceExpiration')->textInput(['maxlength' => true]) ?>

    <?= $form->field($store, 'defaultPaymentMethod')->dropDownList(
        $defaultPaymentMethod,
        [
            'prompt' => Yii::t('app', 'Seleziona la crypto predefinita'), 'id' => 'defaultPaymentMethod'
        ]
    ); ?>


    <?= $form->field($store, 'defaultCurrency')->dropDownList(
        $defaultCurrency,
        [
            'prompt' => Yii::t('app', 'Seleziona la valuta predefinita'), 'id' => 'defaultCurrency'
        ]
    ); ?>


    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Salva'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>