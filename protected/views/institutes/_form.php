<?php
/* @var $this InstitutesController */
/* @var $model Institutes */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'institutes-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

<?php if ($model->isNewRecord){ ?>
<div class="form-group">
	<?php echo $form->labelEx($user,'email'); ?>
	<?php echo $form->textField($user,'email',array('size'=>60,'maxlength'=>200,'class'=>'form-control')); ?>
	<?php echo $form->error($user,'email',array('class'=>'alert alert-danger')); ?>
</div>
<div class="form-group">
	<?php echo $form->labelEx($user,'password'); ?>
	<?php echo $form->passwordField($user,'password',array('size'=>60,'maxlength'=>200,'class'=>'form-control')); ?>
	<?php echo $form->error($user,'password',array('class'=>'alert alert-danger')); ?>
</div>
<?php } ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>200,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'description',array('class'=>'alert alert-danger')); ?>
	</div>

<?php if (!($model->isNewRecord)){ ?>
	<div class="form-group">
		<?php echo $form->labelEx($model,'wallet_address'); ?>
		<?php echo $form->textField($model,'wallet_address',array('size'=>50,'maxlength'=>50,'class'=>'form-control','readonly'=>'readonly')); ?>
		<?php echo $form->error($model,'wallet_address',array('class'=>'alert alert-danger')); ?>
	</div>
<?php } ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'max_wait_time'); ?>
		<p class="text-warning"><i><?php echo Yii::t('lang','If you set the time to zero the alarm will not be activated.'); ?></i></p>
		<?php echo $form->textField($model,'max_wait_time',array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'max_wait_time',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'max_wait_message'); ?>
		<?php echo $form->textField($model,'max_wait_message',array('size'=>60,'maxlength'=>200,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'max_wait_message',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'default_sending_quantity'); ?>
		<?php echo $form->textField($model,'default_sending_quantity',array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'default_sending_quantity',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'min_fund_alert'); ?>
		<?php echo $form->textField($model,'min_fund_alert',array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'min_fund_alert',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'email_fund_alert'); ?>
		<?php echo $form->textField($model,'email_fund_alert',array('size'=>60,'maxlength'=>255,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'email_fund_alert',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
			<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
