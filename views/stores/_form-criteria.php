<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stores */
/* @var $form yii\bootstrap4\ActiveForm */


$store = $model->storesettings;

$select = [
    0 => Yii::t('app', 'Inferiore a'),
    1 => Yii::t('app', 'Superiore a'),
];


$paymentMethodCriteria = json_decode($store->paymentMethodCriteria ?? '');
// echo "<pre>" . print_r($paymentMethodCriteria, true) . "</pre>";
// exit;

$store->paymentMethod_BTC_amount = $paymentMethodCriteria[0]->amount ?? 0;
$store->paymentMethod_BTC_above = $paymentMethodCriteria[0]->above ?? 1;
$store->paymentMethod_LN_amount = $paymentMethodCriteria[1]->amount ?? 0;
$store->paymentMethod_LN_above = $paymentMethodCriteria[1]->above ?? 1;

?>

<div class="stores-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-label mb-1">Abilita i metodi di pagamento solo quando l'importo è …</div>

    <table class="table table-sm mt-0 mx-0">
        <tbody>
            <tr>
                <td class="border-0 ps-0 align-middle">
                    <?= Html::label($store->getAttributeLabel('paymentMethod_BTC_amount'), null, ['class' => '']) ?>
                </td>
                <td class="border-0 ps-0 align-middle">
                    <?= $form->field($store, 'paymentMethod_BTC_above')->dropDownList($select)->label(false) ?>
                </td>
                <td class="border-0 ps-0 align-middle">
                    <?= $form->field($store, 'paymentMethod_BTC_amount', [
                        'options' => ['class' => 'form-group'],
                        'inputTemplate' => '<div class="input-group-prepend"><div class="input-group-text">' . $store->defaultCurrency . '</div>{input}</div>',
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group input-group-sm mb-1'],
                        'inputOptions' => ['autocomplete' => 'off', 'style' => 'width: 100px;'],
                    ])->label(false) ?>
                </td>
            </tr>
            <tr>
                <td class="border-0 ps-0 align-middle">
                    <?= Html::label($store->getAttributeLabel('paymentMethod_LN_amount'), null, ['class' => '']) ?>
                </td>
                <td class="border-0 ps-0 align-middle">
                    <?= $form->field($store, 'paymentMethod_LN_above')->dropDownList($select)->label(false) ?>
                </td>
                <td class="border-0 ps-0 align-middle">
                    <?= $form->field($store, 'paymentMethod_LN_amount', [
                        'options' => ['class' => 'form-group'],
                        'inputTemplate' => '<div class="input-group-prepend"><div class="input-group-text">' . $store->defaultCurrency . '</div>{input}</div>',
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'input-group input-group-sm mb-1'],
                        'inputOptions' => ['autocomplete' => 'off', 'style' => 'width: 100px;'],
                    ])->label(false) ?>
                </td>
            </tr>
        </tbody>
    </table>


    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Salva'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>