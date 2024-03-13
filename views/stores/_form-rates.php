<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stores */
/* @var $form yii\bootstrap4\ActiveForm */

$store = $model->storesettings;

$preferredSource = [
    'swaggy' => 'Swaggy',
    'bitstamp' => 'Bitstamp',
    'binance' => 'Binance',
    'kraken' => 'Kraken',
    'bitfinex' => 'Bitfinex',
];

$defaultPaymentMethod = ['BTC' => 'BTC'];
$defaultCurrency = ['EUR' => 'EUR', 'USD' => 'USD'];


?>

<div class="stores-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($store, 'preferredSource')->dropDownList(
        $preferredSource,
        [
            'prompt' => Yii::t('app', 'Seleziona un exchange'), 'id' => 'preferredSource'
        ]
    ); ?>

    <?= $form->field($store, 'spread')->textInput(['maxlength' => true]) ?>
    
    


    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>