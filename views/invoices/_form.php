<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model app\models\Invoices */
/* @var $form yii\widgets\ActiveForm */

$availableStatusesForManualMarking = json_decode($model->availableStatusesForManualMarking);

// echo $scelta; exit;
$traduzioni = [
    'Settled' => Yii::t('app', 'Settled'),
    'Invalid' => Yii::t('app', 'Invalid'),
];
$selezionaScelta = [];
foreach ($availableStatusesForManualMarking as $text) {
    if (array_key_exists($text, $traduzioni)) {
        $selezionaScelta[$text] = $traduzioni[$text];
    }
}
$model->createdTime = Yii::$app->formatter->asDate(($model->createdTime), 'php:H:i:s d/m/Y');
// echo '<pre>'.print_r($selezionaScelta, true);exit;

?>

<div class="invoices-form">

    <?php $form = ActiveForm::begin([
        // 'layout' => 'horizontal',
    ]); ?>

    <div class="container">
        <div class="row">
            <div class="col-8">
                <?php
                echo $form->field($model, 'createdTime')->textInput([
                    'disabled' => true,
                    'value' => Yii::$app->formatter->asDate($model->createdTime, 'php:H:i:s d/m/Y'),
                ]);
                ?>
            </div>
            <div class="col">

            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'invoiceType')->textInput(['disabled' => 'disabled']) ?>
            </div>
            <div class="col">

            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'amount')->textInput(['disabled' => 'disabled']) ?>
            </div>
            <div class="col">

            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'status')->textInput(['disabled' => 'disabled']) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'updateStatus')->dropDownList(
                    $selezionaScelta,
                    [
                        'prompt' => Yii::t('app', 'Seleziona un nuovo stato'), 'id' => 'updateStatus'
                    ]
                )->label(); ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Aggiorna stato'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>



    <?php ActiveForm::end(); ?>

</div>