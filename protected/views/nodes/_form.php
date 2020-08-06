<?php
/* @var $this StoresController */
/* @var $model Stores */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'nodes-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'url'); ?>
		<?php echo $form->textField($model,'url',array('size'=>60,'maxlength'=>100,'placeholder'=>'Url','class'=>'form-control')); ?>
		<?php echo $form->error($model,'url',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'port'); ?>
		<?php echo $form->textField($model,'port',array('size'=>10,'maxlength'=>10,'placeholder'=>'Porta','class'=>'form-control')); ?>
		<?php echo $form->error($model,'port',array('class'=>'alert alert-danger')); ?>
	</div>


	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
