<?php
/* @var $this ShopsController */
/* @var $model Shops */
/* @var $form CActiveForm */


$ajaxSelectStores = Yii::app()->createUrl('shops/ajaxCreateSelectStore');
$script = <<<JS
	$("#Shops_id_merchant").change(function() {
		var idMerchant = this.value;

		$.ajax({
			url: "{$ajaxSelectStores}",
			dataType: "json",
			data: {
				id: idMerchant
			},
			beforeSend: function() {
				$("#hiddenStores").css("display","");
			},
			success: function(result) {
				//console.log(result);
				var my_list = $("#Shops_id_store").empty();
				$.each(result, function(i, v){
					my_list.append($("<option>").attr('value',i).text(v));
				});
			}
		});
	});
JS;
Yii::app()->clientScript->registerScript('script', $script);
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'shops-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
$criteria=new CDbCriteria();
$criteria->compare('deleted',0,false);

if (Yii::app()->user->objUser['privilegi'] == 20){
	$merchants=Merchants::model()->findAll($criteria);
	$listaMerchants = CHtml::listData( $merchants, 'id_merchant' , 'denomination');
	$listaMerchants[0] = ' ';
	ksort($listaMerchants); // ordino per key in modo tale che [0] è il prmio della lista
}else{
	$merchants=Merchants::model()->findByAttributes(array('id_user'=>Yii::app()->user->objUser['id_user'],'deleted'=>0));
	$criteria->compare('id_merchant',$merchants->id_merchant,false);
}
$stores = Stores::model()->findAll($criteria);
$listaStores = CHtml::listData( $stores, 'id_store' , 'denomination');
#echo "<pre>".print_r($listaStores,true)."</pre>";
#exit;
?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>
	<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
		<div class="form-group">
			<?php echo $form->labelEx($model,'Seleziona il Commerciante'); ?>
			<?php echo $form->dropDownList($model,'id_merchant',$listaMerchants,array('class'=>'form-control'));	?>
			<?php echo $form->error($model,'id_merchant',array('class'=>'alert alert-danger')); ?>
		</div>
		<?php $display = ($model->id_store!=0 ? '' : 'none'); ?>
		<div class="form-group" id='hiddenStores' style="display:<?php echo $display;?>">
			<?php echo $form->labelEx($model,'Seleziona il Negozio'); ?>
			<!-- <select class='form-control' name='Shops[id_store]' id='Shops_id_store'></select> -->
			<?php echo $form->dropDownList($model,'id_store',$listaStores,array('class'=>'form-control'));	?>
			<?php echo $form->error($model,'id_store',array('class'=>'alert alert-danger')); ?>

		</div>
	<?php }else{ ?>
		<div class="form-group">
			<?php echo $form->labelEx($model,'Seleziona il Negozio'); ?>
			<?php echo $form->dropDownList($model,'id_store',$listaStores,array('class'=>'form-control'));	?>
			<?php echo $form->error($model,'id_store',array('class'=>'alert alert-danger')); ?>
			<p class="text-primary"><i>Selezionare il <b>Negozio</b> a cui associare il nuovo Self POS</i></p>
		</div>
	<?php } ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'Descrizione Shop POS'); ?>
		<?php echo $form->textField($model,'denomination',array('size'=>60,'maxlength'=>300,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'denomination',array('class'=>'alert alert-danger')); ?>
		<p class="text-primary"><i>Descrivere il nuovo <b>Self POS</b> secondo l'organizzazione interna (es. Shop principale, Shop piano terra, Shop reparto 1, ecc…)</i></p>
	</div>



	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' =>'btn btn-primary','id'=>'submit_button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
