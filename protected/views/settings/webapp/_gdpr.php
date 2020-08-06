<?php
//carico i paesi
$countryData = WebApp::CountryDataset();

//lista comuni italiani
$listaComuni = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'citta ASC')), 'id_comune', function($descrizione) {
    return CHtml::encode(str_replace("'","`",$descrizione->citta).' ('.$descrizione->sigla.')');
});
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsGdpr-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));?>

<div class="col-md-12">
	<!-- <div class="card alert alert-secondary"> -->
    <div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_titolare'); ?>
				<?php echo $form->textField($model,'gdpr_titolare',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_titolare',array('class'=>'alert alert-danger')); ?>
			</div>
            <div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_vat'); ?>
				<?php echo $form->textField($model,'gdpr_vat',array('size'=>10,'maxlength'=>50,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_vat',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_address'); ?>
				<?php echo $form->textField($model,'gdpr_address',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_address',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_cap'); ?>
				<?php echo $form->textField($model,'gdpr_cap',array('size'=>10,'maxlength'=>10,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_cap',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_city'); ?>
				<?php echo $form->dropDownList($model,'gdpr_city',$listaComuni,array('class'=>'form-control'));	?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_country'); ?>
				<?php echo $form->dropDownList($model,'gdpr_country',$countryData,array('class'=>'form-control'));	?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_telefono'); ?>
				<?php echo $form->textField($model,'gdpr_telefono',array('size'=>50,'maxlength'=>50,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_telefono',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_fax'); ?>
				<?php echo $form->textField($model,'gdpr_fax',array('size'=>50,'maxlength'=>50,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_fax',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_email'); ?>
				<?php echo $form->textField($model,'gdpr_email',array('size'=>50,'maxlength'=>50,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_email',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_dpo_denomination'); ?>
				<?php echo $form->textField($model,'gdpr_dpo_denomination',array('size'=>250,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_dpo_denomination',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_dpo_email'); ?>
				<?php echo $form->textField($model,'gdpr_dpo_email',array('size'=>50,'maxlength'=>50,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_dpo_email',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'gdpr_dpo_telefono'); ?>
				<?php echo $form->textField($model,'gdpr_dpo_telefono',array('size'=>50,'maxlength'=>50,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'gdpr_dpo_telefono',array('class'=>'alert alert-danger')); ?>
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
