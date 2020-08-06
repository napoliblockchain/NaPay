<?php
/* @var $this TipoPagamentiController */
/* @var $model TipoPagamenti */
/* @var $form CActiveForm */

$listaPermessi = [0=>'Utente',20=>'Amministratore'];
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'tipo-pagamenti-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>500,'maxlength'=>50,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'description',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona il tipo di permesso'); ?>
		<?php echo $form->dropDownList($model,'permission',$listaPermessi,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
