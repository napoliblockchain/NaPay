<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsAssociation-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));?>

<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">

			<!-- <div class="form-group">
				<?php //echo $form->labelEx($model,'association_percent'); ?>
				<?php //echo $form->textField($model,'association_percent',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php //echo $form->error($model,'association_percent',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php //echo $form->labelEx($model,'association_receiving_address'); ?>
				<?php //echo $form->textField($model,'association_receiving_address',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php //echo $form->error($model,'association_receiving_address',array('class'=>'alert alert-danger')); ?>
			</div> -->

			<div class="form-group">
				<?php echo $form->labelEx($model,'quota_iscrizione_socio'); ?>
				<?php echo $form->textField($model,'quota_iscrizione_socio',array('size'=>10,'maxlength'=>10,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'quota_iscrizione_socio',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'quota_iscrizione_socioGiuridico'); ?>
				<?php echo $form->textField($model,'quota_iscrizione_socioGiuridico',array('size'=>10,'maxlength'=>10,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'quota_iscrizione_socioGiuridico',array('class'=>'alert alert-danger')); ?>
			</div>
		</div>
	</div>
</div>

<?php //echo $form->hiddenField($model,'step',array('value'=>2)); ?>
<div class="col-md-12">
	<div class="form-group">
		<br>
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>
</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
