<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'stores-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

$gateways=Gateways::model()->findAll();
$listaGateways = CHtml::listData( $gateways, 'id_gateway' , 'denomination');
ksort($listaGateways);


$preferredCoinList = WebApp::getPreferredCoinList();
if (empty($preferredCoinList))
	$preferredCoinList = [];

if ($model->blockchainAsset == '')
	$model->blockchainAsset = "{'BTC':'BTC'}";

$enabledAssets = CJSON::decode($model->blockchainAsset);
$listaServerBlockchain = CHtml::listData(Blockchains::model()->findAll(), 'url', 'denomination');
?>
	<div class="form-group">
		<?php echo $form->labelEx($model,'store_denomination'); ?>
		<?php echo $form->textField($model,'store_denomination',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'store_denomination',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'blockchainAddress'); ?>
		<?php echo $form->dropDownList($model,'blockchainAddress',$listaServerBlockchain,array('class'=>'form-control'));	?>
		<?php echo $form->error($model,'blockchainAddress',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'id_gateway'); ?>
		<?php echo $form->dropDownList($model,'id_gateway',$listaGateways,array('class'=>'form-control'));	?>
	</div>
	<?php echo $form->hiddenField($model,'id_gateway',array('value'=>1)); ?>

	<div class="row form-group ">
		<div class="col col-md-3">
			<label class=" form-control-label"><?php echo $form->labelEx($model,'blockchainAsset'); ?></label>
		</div>

		<div class="col col-md-9">
			<div class="form-check">
				<?php
				foreach ($preferredCoinList as $key => $value) {
					$checked = '';
					if (array_key_exists($key,$enabledAssets))
						$checked = 'checked="checked"';
					?>

					<div class="checkbox">
						<label for="checkbox_<?php echo $key;?>" class="form-check-label ">
							<input <?php echo $checked;?> type="checkbox" name="blockchainAsset[<?php echo $key;?>]" value="<?php echo $key;?>" class="form-check-input"><?php echo $value;?>
						</label>

					</div>
				<?php } ?>
			</div>
		</div>
	</div>




	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
