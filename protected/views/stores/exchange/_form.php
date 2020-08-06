<div class="form">

<?php

//$allExchanges=Exchanges::model()->findAll();
//$listaExchanges = CHtml::listData( $allExchanges, 'id_exchange' , 'denomination');


$default_currency_pairs = [''=>'','BTC_EUR'=>'BTC_EUR'];

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
		<?php echo $form->labelEx($model,'Seleziona l\'exchange'); ?>
		<?php echo $form->dropDownList($model,'preferred_exchange',$preferredPriceSource,array('class'=>'form-control'));	?>
		<?php echo $form->error($model,'preferred_exchange',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'spread', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'spread',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'spread',array('class'=>'alert alert-danger')); ?>
	</div>


	<div class="form-group">
		<?php echo $form->labelEx($model,'default_currency_pairs'); ?>
		<?php echo $form->dropDownList($model,'default_currency_pairs',$default_currency_pairs,array('class'=>'form-control'));	?>
	</div>
	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
