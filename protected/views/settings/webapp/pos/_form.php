<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'pos-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
$criteria=new CDbCriteria();
$criteria->compare('deleted',0,false);
$criteria->compare('id_merchant',0,false);

$stores = Stores::model()->findAll($criteria);
$listaStores = CHtml::listData( $stores, 'id_store' , 'denomination');
#echo "<pre>".print_r($listaStores,true)."</pre>";
#exit;
?>


	<div class="form-group">
		<?php echo $form->labelEx($model,'Seleziona il Negozio'); ?>
		<?php echo $form->dropDownList($model,'id_store',$listaStores,array('class'=>'form-control'));	?>
		<?php echo $form->error($model,'id_store',array('class'=>'alert alert-danger')); ?>
		<p class="text-primary"><i>Selezionare il <b>Negozio</b> a cui associare il nuovo POS</i></p>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'pos_denomination'); ?>
		<?php echo $form->textField($model,'pos_denomination',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'pos_denomination',array('class'=>'alert alert-danger')); ?>
		<p class="text-primary"><i>Descrivere il <b>POS</b></i></p>
	</div>

	<div class="form-group">
	Conferma inserimento <?php echo "<input name='SettingsWebappForm[confirmPos]' id='SettingsWebappForm_confirmPos' type='checkbox' >"; ?>
	</div>


	<div class="form-group">
		<?php echo CHtml::submitButton('Salva', array('class' =>'btn btn-primary','id'=>'submit_button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
