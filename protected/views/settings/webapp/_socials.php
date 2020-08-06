<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'socials-form',
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
			<div class="card border border-primary">
				<div class="card-header">
					<strong class="card-title">Google</strong>
				</div>
				<div class="card-body">
					<div class="form-group">
						<?php echo $form->labelEx($model,'GoogleOauthClientId'); ?>
						<?php echo $form->textField($model,'GoogleOauthClientId',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
						<?php echo $form->error($model,'GoogleOauthClientId',array('class'=>'alert alert-danger')); ?>
					</div>

					<div class="form-group">
						<?php echo $form->labelEx($model,'GoogleOauthClientSecret'); ?>
						<?php echo $form->passwordField($model,'GoogleOauthClientSecret',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
						<?php echo $form->error($model,'GoogleOauthClientSecret',array('class'=>'alert alert-danger')); ?>
					</div>
				</div>
			</div>

			<div class="card border border-primary">
				<div class="card-header">
					<strong class="card-title">Facebook</strong>
				</div>
				<div class="card-body">
					<div class="form-group">
						<?php echo $form->labelEx($model,'facebookAppID'); ?>
						<?php echo $form->textField($model,'facebookAppID',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
						<?php echo $form->error($model,'facebookAppID',array('class'=>'alert alert-danger')); ?>
					</div>

					<div class="form-group">
						<?php echo $form->labelEx($model,'facebookAppVersion'); ?>
						<?php echo $form->textField($model,'facebookAppVersion',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
						<?php echo $form->error($model,'facebookAppVersion',array('class'=>'alert alert-danger')); ?>
					</div>
				</div>

			</div>

			<div class="card border border-primary">
				<div class="card-header">
					<strong class="card-title">Telegram</strong>
				</div>
				<div class="card-body">
					<div class="form-group">
						<?php echo $form->labelEx($model,'telegramBotName'); ?>
						<?php echo $form->textField($model,'telegramBotName',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
						<?php echo $form->error($model,'telegramBotName',array('class'=>'alert alert-danger')); ?>
					</div>

					<div class="form-group">
						<?php echo $form->labelEx($model,'telegramToken'); ?>
						<?php echo $form->passwordField($model,'telegramToken',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
						<?php echo $form->error($model,'telegramToken',array('class'=>'alert alert-danger')); ?>
					</div>
				</div>
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
