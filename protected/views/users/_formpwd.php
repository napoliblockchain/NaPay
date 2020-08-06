<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
)); ?>
<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>


		<div class="form-group">
			<?php $model->password = ''; ?>
			<?php echo $form->labelEx($model,'Inserire la password attuale'); ?>
			<?php echo $form->passwordField($model,'password',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'password',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'Inserire la nuova password'); ?>
			<?php echo $form->passwordField($model,'new_password',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'new_password',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'Ripetere la nuova password'); ?>
			<?php echo $form->passwordField($model,'new_password_confirm',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'new_password_confirm',array('class'=>'alert alert-danger')); ?>
		</div>

		<?php //echo $form->hiddenField($model,'id_users_type'); ?>
		<?php //echo $form->hiddenField($model,'id_carica'); ?>
		<?php //echo $form->hiddenField($model,'email'); ?>
		<?php //echo $form->hiddenField($model,'name'); ?>
		<?php //echo $form->hiddenField($model,'surname'); ?>
		<?php //echo $form->hiddenField($model,'activation_code'); ?>
		<?php //echo $form->hiddenField($model,'status_activation_code'); ?>

	<div class="form-group">
		<?php echo CHtml::submitButton(('Conferma'), array('class' => 'btn btn-primary')); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->
