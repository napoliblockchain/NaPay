<?php
/* @var $this  */
/* @var $model  */
/* @var $form CActiveForm */


?>

<div class="form">

<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'products-form',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('enctype' => 'multipart/form-data'),
	)
);

?>

<div class="form-group">
    <?php echo $form->labelEx($model,'title'); ?>
    <?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
    <?php echo $form->error($model,'title',array('class'=>'alert alert-danger')); ?>
</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'description',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'price'); ?>
		<?php echo $form->textField($model,'price',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'price',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'filename'); ?>
		<?php echo $form->fileField($model,'filename',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'filename',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' =>'btn btn-primary','id'=>'submit_button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
