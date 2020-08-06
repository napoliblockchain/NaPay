<div class="form">

<?php


foreach ($DefaultLang as $index => $descri){
	$languages[$index] = htmlspecialchars_decode($descri);

}
// echo "<pre>".print_r($DefaultLang,true)."</pre>";
// echo "<pre>".print_r($languages,true)."</pre>";
// exit;


//$allExchanges=Exchanges::model()->findAll();
//$listaExchanges = CHtml::listData( $allExchanges, 'id_exchange' , 'denomination');


//$default_payment_method = ['BTC'=>'BTC'];
//$default_lang = ['it-IT'=>'Italiano','en'=>'Inglese','fr-FR'=>'Francese','es-ES'=>'Spagnolo'];
$yesOrNot = ['false'=>' ','false'=>'No','true'=>'Si'];


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

	<div class="form-group">
		<?php echo $form->labelEx($model,'HtmlTitle', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'HtmlTitle',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'HtmlTitle',array('class'=>'alert alert-danger')); ?>
	</div>


	<div class="form-group">
		<?php echo $form->labelEx($model,'DefaultPaymentMethod'); ?>
		<?php echo $form->dropDownList($model,'DefaultPaymentMethod',$DefaultPaymentMethod,array('class'=>'form-control'));	?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'DefaultLang'); ?>
		<select class="form-control" name="StoreForm[DefaultLang]" id="StoreForm_DefaultLang">
		<?php

		foreach ($languages as $index => $value)	{
			$selected = ($index == 'it-IT' ?  'selected="selected"' : '');

			echo "<option $selected value='".$index."'>".$value."</option>";
		}
		?>
	</select>
		<?php //echo $form->dropDownList($model,'DefaultLang',$languages,array('class'=>'form-control'));	?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'RequiresRefundEmail'); ?>
		<?php echo $form->dropDownList($model,'RequiresRefundEmail',$yesOrNot,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'OnChainMinValue', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'OnChainMinValue',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'OnChainMinValue',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'LightningMaxValue', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'LightningMaxValue',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'LightningMaxValue',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'LightningAmountInSatoshi'); ?>
		<?php echo $form->dropDownList($model,'LightningAmountInSatoshi',$yesOrNot,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'RedirectAutomatically'); ?>
		<?php echo $form->dropDownList($model,'RedirectAutomatically',$yesOrNot,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'ShowRecommendedFee'); ?>
		<?php echo $form->dropDownList($model,'ShowRecommendedFee',$yesOrNot,array('class'=>'form-control'));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'RecommendedFeeBlockTarget', array('class'=>'text-primary')); ?>
		<?php echo $form->textField($model,'RecommendedFeeBlockTarget',array(
			'class'=>'form-control',
		)); ?>
		<?php echo $form->error($model,'RecommendedFeeBlockTarget',array('class'=>'alert alert-danger')); ?>
	</div>


	<?php echo $form->hiddenField($model,'command'); ?>



	<div class="form-group">
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
