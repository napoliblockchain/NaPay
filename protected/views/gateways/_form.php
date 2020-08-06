<?php
/* @var $this StoresController */
/* @var $model Stores */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'gateways-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'denomination'); ?>
		<?php echo $form->textField($model,'denomination',array('size'=>60,'maxlength'=>250,'placeholder'=>'Denominazione','class'=>'form-control')); ?>
		<?php echo $form->error($model,'denomination',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'action_controller'); ?>
		<?php echo $form->textField($model,'action_controller',array('size'=>50,'maxlength'=>50,'placeholder'=>'Action for Controllers ','class'=>'form-control')); ?>
		<?php echo $form->error($model,'action_controller',array('class'=>'alert alert-danger')); ?>
	</div>



	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
