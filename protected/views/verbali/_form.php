<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'verbali-form',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('enctype' => 'multipart/form-data'),
));

if ($model->isNewRecord){
	$model->data_verbale = date('d/m/Y',time());
}else{
	$model->data_verbale = WebApp::data_it($model->data_verbale);
	#echo $model->url_verbale;
}
?>


	<div class="form-group">
		<?php //echo $form->labelEx($model,'data_verbale'); ?>
		<?php echo $form->textFieldRow($model,'data_verbale',array('class'=>'form-control')); ?>
		<?php //echo $form->error($model,'data_verbale',array('class'=>'alert alert-danger')); ?>
		<!-- è utile cambiare il css degli errori nella classe "help-block error" -->
	</div>

	<div class="form-group">
		<?php //echo $form->labelEx($model,'Descrizione Verbale'); ?>
		<?php echo $form->textFieldRow($model,'descrizione_verbale',array('size'=>60,'maxlength'=>500,'class'=>'form-control')); ?>
		<?php // echo $form->error($model,'descrizione_verbale',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Carica File'); ?>
		<?php echo $form->fileField($model,'tempfile',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'url_verbale',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>$model->isNewRecord ? 'Inserisci' : 'Salva',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
