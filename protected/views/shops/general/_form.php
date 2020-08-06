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
		<?php echo $form->labelEx($model,'Title', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'Title',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'Title',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->checkbox($model,'ShowCustomAmount'); ?>
		<?php echo $form->labelEx($model,'ShowCustomAmount', array('class'=>'text-primary')); ?>
		<?php echo $form->error($model,'ShowCustomAmount',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->checkbox($model,'ShowDiscount'); ?>
		<?php echo $form->labelEx($model,'ShowDiscount', array('class'=>'text-primary')); ?>
		<?php echo $form->error($model,'ShowDiscount',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->checkbox($model,'EnableTips'); ?>
		<?php echo $form->labelEx($model,'EnableTips', array('class'=>'text-primary')); ?>
		<?php echo $form->error($model,'EnableTips',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'ButtonText', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'ButtonText',array(
			'class'=>'form-control',
			'placeholder'=>'Buy for {0}'
		)); ?>
		<?php echo $form->error($model,'ButtonText',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'CustomButtonText', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'CustomButtonText',array(
			'class'=>'form-control',
			'placeholder'=>'Pay'
		)); ?>
		<?php echo $form->error($model,'CustomButtonText',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'CustomTipText', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'CustomTipText',array(
			'class'=>'form-control',
			'placeholder'=>'Do you want to leave a tip?'
		)); ?>
		<?php echo $form->error($model,'CustomTipText',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'CustomTipPercentages', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'CustomTipPercentages',array(
			'class'=>'form-control',
			'placeholder'=>'15,18,20'
		)); ?>
		<?php echo $form->error($model,'CustomTipPercentages',array('class'=>'alert alert-danger')); ?>
	</div>
	
	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
