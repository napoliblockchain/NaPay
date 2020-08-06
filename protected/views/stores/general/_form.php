<div class="form">

<?php

$networkfee = ['Always'=>'Sempre','Never'=>'Mai','MultiplePaymentsOnly'=>'Solo pagamenti multipli'];
$conferme = [0=>'0',1=>'1',2=>'2',6=>'6'];

$this->pageTitle=Yii::app()->name . ' - Store';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'store-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
?>


	<p><i class="text-danger">I campi con * sono obbligatori</i>


	<div class="form-group">
		<?php echo $form->labelEx($model,'store_denomination', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'store_denomination',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'store_denomination',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'store_website', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'store_website',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'store_website',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'network_fee_mode'); ?>
		<?php echo $form->dropDownList($model,'network_fee_mode',$networkfee,array('class'=>'form-control'));	?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'invoice_expiration', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'invoice_expiration',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'invoice_expiration',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'monitoring_expiration', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'monitoring_expiration',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'monitoring_expiration',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'payment_tolerance', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'payment_tolerance',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'payment_tolerance',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'speed_policy'); ?>
		<?php echo $form->dropDownList($model,'speed_policy',$conferme,array('class'=>'form-control'));	?>
	</div>
	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
