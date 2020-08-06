<?php
/* @var $this StatutoController */
/* @var $model Statuto */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'cariche-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
$allRuolo=UsersType::model()->findAll();
$listaRuoli = CHtml::listData( $allRuolo, 'id_users_type' , 'desc');
$disabled = 'disabled';
if ($model->isNewRecord)
	$disabled = '';

?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona il Ruolo'); ?>
		<?php echo $form->dropDownList($model,'id_users_type',$listaRuoli,array("disabled" => $disabled,'class'=>'form-control'));	?>
	</div>


	<div class="form-group">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>500,'maxlength'=>50,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'description',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
