<div class="form">

<?php
$this->pageTitle=Yii::app()->name . ' - Shop';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'shop-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Template', array('class'=>'text-primary')); ?>
		<?php echo $form->textArea($model,'Template',array(
			'class'=>'form-control',
			'maxlength' => 15000, 'rows' => 12, 'cols' => 50
		)); ?>
		<?php echo $form->error($model,'Title',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
