<?php
$countryData = WebApp::CountryDataset();
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsBank-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));?>

<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">

		<div class="card-body ">
			<div class="card-header bg-secondary">
			<i class="card-title text-dark"><small>Le informazioni devono coincidere con quelle inserite nell'Exchange</small></i>
		</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_name'); ?>
				<?php echo $form->textField($model,'bank_name',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'bank_name',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_iban'); ?>
				<?php echo $form->textField($model,'bank_iban',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'bank_iban',array('class'=>'alert alert-danger')); ?>
			</div>


			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_bic'); ?>
				<?php echo $form->textField($model,'bank_bic',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'bank_bic',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_address'); ?>
				<?php echo $form->textField($model,'bank_address',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'bank_address',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_postal_code'); ?>
				<?php echo $form->textField($model,'bank_postal_code',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'bank_postal_code',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_city'); ?>
				<?php echo $form->textField($model,'bank_city',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'bank_city',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_country'); ?>
				<?php echo $form->dropDownList($model,'bank_country',$countryData,array('class'=>'form-control'));	?>
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
