<?php



$exchanges=Exchanges::model()->findAll();
$listaExchanges = CHtml::listData( $exchanges, 'id_exchange' , 'denomination');
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsExchange-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));?>

<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<div class="form-group">
				<?php echo $form->labelEx($model,'Seleziona Exchange'); ?>
				<?php echo $form->dropDownList($model,'id_exchange',$listaExchanges,array('class'=>'form-control'));	?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'only_for_bitstamp_id'); ?>
				<?php echo $form->textField($model,'only_for_bitstamp_id',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'only_for_bitstamp_id',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'exchange_key'); ?>
				<?php echo $form->textField($model,'exchange_key',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'exchange_key',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php $model->exchange_secret = crypt::Decrypt($model->exchange_secret); ?>
				<?php echo $form->labelEx($model,'exchange_secret'); ?>
				<?php echo $form->passwordField($model,'exchange_secret',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'exchange_secret',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'withdrawal_exchange_key'); ?>
				<?php echo $form->textField($model,'withdrawal_exchange_key',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'withdrawal_exchange_key',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php $model->exchange_secret = crypt::Decrypt($model->exchange_secret); ?>
				<?php echo $form->labelEx($model,'withdrawal_exchange_secret'); ?>
				<?php echo $form->passwordField($model,'withdrawal_exchange_secret',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'withdrawal_exchange_secret',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'only_for_bitstamp_liquidation_deposit_address'); ?>
				<?php echo $form->textField($model,'only_for_bitstamp_liquidation_deposit_address',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'only_for_bitstamp_liquidation_deposit_address',array('class'=>'alert alert-danger')); ?>
			</div>

		</div>
	</div>
</div>

<div class="col-md-12">
	<div class="form-group">
		<br>
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>
</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
