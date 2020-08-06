<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsVapid-form',
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
			<p class="text-primary">Application server keys are used to identify your application server with a push service. Click on link to generate new keys.<br>
			<a target="_blank" class="text-danger" href="https://web-push-codelab.glitch.me/">https://web-push-codelab.glitch.me</a>
		</p>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'VapidPublic'); ?>
				<?php echo $form->textField($model,'VapidPublic',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'VapidPublic',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'VapidSecret'); ?>
				<?php echo $form->passwordField($model,'VapidSecret',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'VapidSecret',array('class'=>'alert alert-danger')); ?>
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
