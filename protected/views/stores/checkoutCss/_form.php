<div class="form">

<?php
$this->pageTitle=Yii::app()->name . ' - Store';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'store-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'htmlOptions' => array('enctype' => 'multipart/form-data'),
));
?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Carica CSS'); ?>
		<?php echo $form->fileField($model,'CustomCSS',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'CustomCSS',array('class'=>'alert alert-danger')); ?>
	</div>

	<?php echo $form->hiddenField($model,'DefaultPaymentMethod'); ?>
	<?php echo $form->hiddenField($model,'DefaultLang'); ?>
	<?php echo $form->hiddenField($model,'ShowRecommendedFee'); ?>
	<?php echo $form->hiddenField($model,'RecommendedFeeBlockTarget'); ?>
	<?php echo $form->hiddenField($model,'LightningAmountInSatoshi'); ?>
	<?php echo $form->hiddenField($model,'RedirectAutomatically'); ?>
	<?php echo $form->hiddenField($model,'command'); ?>


	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
