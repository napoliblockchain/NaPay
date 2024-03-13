<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model app\models\Pos */
/* @var $form yii\widgets\ActiveForm */

if ($model->isNewRecord) {
    $created_at = new \DateTime('now');
    $create_date = $created_at->format('Y-m-d');
    $model->create_date = $create_date;
    $model->historical = 0;
}
?>

<div class="pos-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="txt-left">
        <?= $form->errorSummary($model, ['id' => 'error-summary', 'class' => 'col-lg-12 callout callout-danger']) ?>
    </div>

    <?= $form->field($model, 'merchant_id')->dropDownList(
        $merchants_list,
        [
            'prompt' => Yii::t('app', 'Seleziona un esercente'), 'id' => 'merchant_id'
        ]
    ); ?>

    <?php
    echo $form->field($model, 'store_id')->widget(DepDrop::class, [
        'options' => [
            'id' => 'store_id',
            'prompt' => Yii::t('app', 'Negozio'),
        ],
        'type' => DepDrop::TYPE_SELECT2,
        // 'select2Options' => ['pluginOptions' => ['allowClear' => true]],
        'pluginOptions' => [
            'initialize' => $model->isNewRecord ? false : true,
            'depends' => ['merchant_id'],
            'url' => Url::to(['stores/lista-negozi', 'id' => 0])

        ],
    ])->label('Store');
    ?>

    <?= $form->field($model, 'appName')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'create_date')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'close_date')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'historical')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Salva'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>