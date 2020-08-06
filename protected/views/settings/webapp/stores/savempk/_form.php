<div class="form">

<?php

//$allExchanges=Exchanges::model()->findAll();
//$listaExchanges = CHtml::listData( $allExchanges, 'id_exchange' , 'denomination');
$guidaLink = 'https://napoliblockchain.it/esportare-la-chiave-pubblica-mpk-dal-wallet-coinomi/';

$AddressType = ['P2WPKH'=>'Native Segwit','PS2H-P2WPKH'=>'Segwit compatibile','P2PKH'=>'Legacy'];


$this->pageTitle=Yii::app()->name . ' - Store';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'store-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
?>

	<?php echo $form->error($model,'CustomLogo',array('class'=>'alert alert-danger')); ?>
	<p><i class="text-danger">I campi con * sono obbligatori</i>

	<div class="form-group">
		<?php echo $form->labelEx($model,'asset', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'asset',array(
			'value'=>$asset,
			'class'=>'form-control alert-success',
			'readonly'=>'readonly'
		)); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'AddressType'); ?>
		<?php echo $form->dropDownList($model,'AddressType',$AddressType,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'DerivationScheme', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'DerivationScheme',array(
			'class'=>'form-control',
		)); ?>
		<p class="text-primary">
			<i>Inserire la MPK (<strong>Master Public Key</strong>) che devi estrarre dal tuo <b>wallet bitcoin</b> preferito. Se non sai come si fa, clicca sulla <strong><a href="<?php echo $guidaLink; ?>" target="_blank" class="text-danger">guida</a></strong> oppure contatta uno dei nostri associati.</i>
		</p>
		<?php echo $form->error($model,'DerivationScheme',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
