<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Pos */
/* @var $form yii\widgets\ActiveForm */

$pos = $model->possettings;
?>

<div class="pos-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'merchant_id')->dropDownList($merchants_list, ['disabled' => 'disabled']) ?>

    <?= $form->field($model, 'store_id')->dropDownList($stores_list, ['disabled' => 'disabled']) ?>


    <?= $form->field($model, 'appName')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::t('app', 'Salva'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>