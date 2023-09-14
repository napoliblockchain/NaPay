<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;



/* @var $this yii\web\View */
/* @var $model app\models\Commercianti */
/* @var $form yii\widgets\ActiveForm */

if ($model->isNewRecord) {
    $created_at = new \DateTime('now');
    $create_date = $created_at->format('Y-m-d');
    $model->create_date = $create_date;
    $model->historical = 0;
}
?>

<div class="commercianti-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="txt-left">
        <?= $form->errorSummary($model, ['id' => 'error-summary', 'class' => 'col-lg-12 callout callout-warning']) ?>
    </div>

       
    <?= $form->field($model, 'swg_companyId')->textInput(['maxlength' => true]) ?>


    <?= $form->field($model, 'create_date')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'close_date')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'historical')->hiddenInput()->label(false) ?>



    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Salva'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>