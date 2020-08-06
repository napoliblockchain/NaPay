<?php
/* @var $this QuoteController */
/* @var $model Quote */
/* @var $form CActiveForm */
$scadenza = ['No','Si'];
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'quote-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>500,'maxlength'=>50,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'description',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona la scadenza'); ?>
		<?php echo $form->dropDownList($model,'extension',$scadenza,array('class'=>'form-control'));	?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'importo'); ?>
		<?php echo $form->textField($model,'importo',array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'importo',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
