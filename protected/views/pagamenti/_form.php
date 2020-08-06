<?php
/* @var $this PagamentiController */
/* @var $model Pagamenti */
/* @var $form CActiveForm */



//lista quote
$listaQuote = CHtml::listData(Quote::model()->findAll(), 'id_quota', function($quote) {
    return CHtml::encode($quote->description);// . ': €. ' . $quote->importo);
});

//lista tipi pagamento
$tipopagamenti=TipoPagamenti::model()->findAll();
$listaTipoPagamenti = CHtml::listData( $tipopagamenti, 'id_tipo_pagamento' , 'description');

$disabled = 'disabled';
if ($model->isNewRecord){
	$disabled = '';

    $yearEnd = date('Y-m-d', strtotime('last day of december'));

	$model->data_registrazione = date('d/m/Y',time());
	$model->data_inizio		= date('d/m/Y',time());
	// $model->data_scadenza	= date('d/m/Y',time()+ 60*60*24*365); //+ 1 anno
    $model->data_scadenza	= WebApp::data_it($yearEnd);

}else{
	$model->data_registrazione = WebApp::data_it($model->data_registrazione);
	$model->data_inizio		= WebApp::data_it($model->data_inizio);
	$model->data_scadenza	= WebApp::data_it($model->data_scadenza);
}

?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'pagamenti-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<?php //echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona l\'utente'); ?>
		<?php echo $form->dropDownList($model,'id_user',$listaUtenti,array("disabled" => $disabled,'class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona il tipo di iscrizione'); ?>
		<?php echo $form->dropDownList($model,'id_quota',$listaQuote,array('class'=>'form-control'));	?>
		<div class="input-group">
			<?php //$model->importo = 0; ?>
			<?php echo $form->labelEx($model,'importo', array('class'=>'input-group-addon')); ?>
			<?php echo $form->numberField($model,'importo',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
            <?php echo $form->error($model,'importo',array('class'=>'alert alert-danger')); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona il tipo di pagamento'); ?>
		<?php echo $form->dropDownList($model,'id_tipo_pagamento',$listaTipoPagamenti,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'data_registrazione'); ?>
		<?php echo $form->textField($model,'data_registrazione',array('class'=>'form-control','readonly'=>'true')); ?>
		<?php echo $form->error($model,'data_registrazione',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'data_inizio'); ?>
		<?php echo $form->textField($model,'data_inizio',array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'data_inizio',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'data_scadenza'); ?>
		<?php echo $form->textField($model,'data_scadenza',array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'data_scadenza',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : ' Conferma '), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
