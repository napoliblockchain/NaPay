<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsRecaptcha-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

?>


<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<div class="form-group">
				<p class="text-primary">Inserire le chiavi per la sicurezza di Google reCaptcha2
				</p>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'reCaptcha2PublicKey'); ?>
				<?php echo $form->textField($model,'reCaptcha2PublicKey',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'reCaptcha2PublicKey',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'reCaptcha2PrivateKey'); ?>
				<?php echo $form->passwordField($model,'reCaptcha2PrivateKey',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'reCaptcha2PrivateKey',array('class'=>'alert alert-danger')); ?>
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
