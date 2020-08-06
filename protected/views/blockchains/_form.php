<?php
/* @var $this StoresController */
/* @var $model Stores */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'blockchains-form',
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
		<?php echo $form->labelEx($model,'url'); ?>
		<?php echo $form->textField($model,'url',array('size'=>50,'maxlength'=>50,'placeholder'=>'Url','class'=>'form-control')); ?>
		<?php echo $form->error($model,'url',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email',array('size'=>50,'maxlength'=>50,'placeholder'=>'Email','class'=>'form-control')); ?>
		<?php echo $form->error($model,'email',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->textField($model,'password',array('size'=>50,'maxlength'=>50,'placeholder'=>'Password','class'=>'form-control')); ?>
		<?php echo $form->error($model,'password',array('class'=>'alert alert-danger')); ?>
	</div>


	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
