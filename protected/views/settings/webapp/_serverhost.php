
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsServerhost-form',
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
				<?php $model->sshhost = crypt::Decrypt($model->sshhost); ?>
				<?php echo $form->labelEx($model,'sshhost'); ?>
				<?php echo $form->textField($model,'sshhost',array('size'=>60,'maxlength'=>250,'placeholder'=>'ssh host','class'=>'form-control')); ?>
				<?php echo $form->error($model,'sshhost',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php $model->sshuser = crypt::Decrypt($model->sshuser); ?>
				<?php echo $form->labelEx($model,'sshuser'); ?>
				<?php echo $form->textField($model,'sshuser',array('size'=>60,'maxlength'=>250,'placeholder'=>'ssh user','class'=>'form-control')); ?>
				<?php echo $form->error($model,'sshuser',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php $model->sshpassword = crypt::Decrypt($model->sshpassword); ?>
				<?php echo $form->labelEx($model,'sshpassword'); ?>
				<?php echo $form->passwordField($model,'sshpassword',array('size'=>60,'maxlength'=>250,'placeholder'=>'ssh password','class'=>'form-control')); ?>
				<?php echo $form->error($model,'sshpassword',array('class'=>'alert alert-danger')); ?>
			</div>

			<!-- <div class="form-group">
				<?php //$model->rpchost = crypt::Decrypt($model->rpchost); ?>
				<?php //echo $form->labelEx($model,'rpchost'); ?>
				<?php //echo $form->textField($model,'rpchost',array('size'=>60,'maxlength'=>250,'placeholder'=>'rpc host','class'=>'form-control')); ?>
				<?php //echo $form->error($model,'rpchost',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php //$model->rpcport = crypt::Decrypt($model->rpcport); ?>
				<?php //echo $form->labelEx($model,'rpcport'); ?>
				<?php //echo $form->textField($model,'rpcport',array('size'=>60,'maxlength'=>250,'placeholder'=>'rpc port','class'=>'form-control')); ?>
				<?php //echo $form->error($model,'rpcport',array('class'=>'alert alert-danger')); ?>
			</div> -->
		</div>


	</div>
</div>

<?php //echo $form->hiddenField($model,'step',array('value'=>3)); ?>
<div class="col-md-12">
	<div class="form-group">
		<br>
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>
</div>



<?php $this->endWidget(); ?>

</div><!-- form -->
