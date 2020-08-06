<?php

?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'gateway-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

$gateways=Gateways::model()->findAll();
$listaGateways = CHtml::listData( $gateways, 'id_gateway' , 'denomination');
$listaGateways[0]='';
ksort($listaGateways);
?>


<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<div class="form-group">
				<?php echo $form->labelEx($model,'Seleziona Gateway'); ?>
				<?php echo $form->dropDownList($model,'id_gateway',$listaGateways,array('class'=>'form-control','disabled'=>'disabled'));	?>
			</div>
		</div>
	</div>
</div>
<div class="form-group">
	<br>
	<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
</div>
<?php $this->endWidget(); ?>

</div><!-- form -->
