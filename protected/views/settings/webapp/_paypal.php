<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsPaypal-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

$listaModi = ['sandbox'=>'Test','live'=>'Produzione'];
?>


<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<div class="form-group">
				<p class="text-primary">Selezionare le chiavi per i pagamenti tramite Paypal
				</p>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'PAYPAL_CLIENT_ID'); ?>
				<?php echo $form->textField($model,'PAYPAL_CLIENT_ID',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'PAYPAL_CLIENT_ID',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'PAYPAL_CLIENT_SECRET'); ?>
				<?php echo $form->passwordField($model,'PAYPAL_CLIENT_SECRET',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'PAYPAL_CLIENT_SECRET',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'Seleziona Modo'); ?>
				<?php echo $form->dropDownList($model,'PAYPAL_MODE',$listaModi,array('class'=>'form-control'));	?>
			</div>
		</div>
	</div>
</div>
<?php //echo $form->hiddenField($model,'step',array('value'=>4)); ?>
<div class="col-md-12">
	<div class="form-group">
		<br>
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>
</div>



<?php $this->endWidget(); ?>

</div><!-- form -->
