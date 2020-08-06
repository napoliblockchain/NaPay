<?php
/* @var $this StoresController */
/* @var $model Stores */
/* @var $form CActiveForm */
?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'stores-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
//carico i paesi
$countryData = WebApp::CountryDataset();

//CARICO PROVINCE E COMUNI
$listaProvince = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'sigla ASC')), 'sigla', function($descrizione) {
	 return $descrizione->sigla.' ('.$descrizione->provincia.')';
});
$listaProvince[]=' ';
asort($listaProvince);

$listaComuni = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'citta ASC')), 'id_comune', function($descrizione) {
	 return $descrizione->citta.' ('.$descrizione->sigla.')';
});


$optionProvince = "";
if (isset($model->city) && $model->city > 0)
	$optionProvince = ComuniItaliani::model()->findByPk($model->city)->sigla;

$ajaxSelectbyCap = Yii::app()->createUrl('siteBackend/selectComuneByCap');
$ajaxSelectProvince = Yii::app()->createUrl('siteBackend/ajaxSelectComune');
$ajaxSelectMerchantAddress = Yii::app()->createUrl('stores/ajaxSelectMerchantAddress');

$script = <<<JS
	var formName = 'Stores'; //da cambiare in base al Form di appartenenza

	var cap = document.querySelector('#'+formName+'_cap');
	var provincia = document.querySelector('#'+formName+'_provincia');
	//provincia.addEventListener('change', function(){
	$('#'+formName+'_provincia').change(function(){
		var codice = this.value;
		$.ajax({
			url: "{$ajaxSelectProvince}",
			dataType: "json",
			data: {
				id: codice
			},
			success: function(json) {
				$('#'+formName+'_city').prop("disabled", false); // Element(s) are now enabled.
				$('#'+formName+'_city').empty(); //Elements are now empty
				$.each(json, function(index,descri) {
					$('#'+formName+'_city').append(new Option(descri, index));
				});
			}
		});
	});

	cap.addEventListener('keyup', function(){
        var cap = this.value;
        console.log('lunghezza cap',cap.length);

        if (cap.length == 5){
            $.ajax({
    			url: "{$ajaxSelectbyCap}",
    			dataType: "json",
    			data: {
    				cap: cap
    			},
    			success: function(json) {
                    console.log('risposta da cap',json);
                    if (json.success == 1){
                        // imposto la provincia con il valore restituito e innesco il cambio sul field seguente
                        $('#'+formName+'_provincia').val( json.sigla ).trigger("change");

                    }
    			}
    		});
        }
    });

	$("#Stores_stesso_indirizzo").change(function() {
		var risposta = this.value;
		if (risposta==0){
			$('#Stores_address').val('');
			$('#Stores_cap').val('');
			$('#Stores_city').val('');
			$('#Stores_county').val('');
			$('#Stores_vat').val('');
			$('#Stores_provincia').val('');
			$('#sameAddressSelect').hide();

		}
	});

	$("#Stores_id_merchant").change(function() {
		var idMerchant = this.value;
		$.ajax({
			url: "{$ajaxSelectMerchantAddress}",
			dataType: "json",
			data: {
				id: idMerchant
			},
			beforeSend: function() {
				$(".hiddenSameAddress").css("display","");
			},
			success: function(json) {
				$('#Stores_address').val(json.address);
				$('#Stores_cap').val(json.cap);
				$('#Stores_city').val(json.city);
				$('#Stores_county').val(json.county);
				$('#Stores_vat').val(json.vat);
				$('#Stores_provincia').val(json.provincia);
			}
		});
	});


JS;

Yii::app()->clientScript->registerScript('script', $script);


$criteria=new CDbCriteria();
$criteria->compare('deleted',0,false);

$allMerchants=Merchants::model()->findAll($criteria);
$listaMerchants = CHtml::listData( $allMerchants, 'id_merchant' , 'denomination');
$listaMerchants[0] = ' ';
ksort($listaMerchants); // ordino per key in modo tale che [0] è il prmio della lista
$disabled = 'disabled';
if ($model->isNewRecord)
	$disabled = '';

$stessoIndirizzo = ['Si','No'];
$newMerchant = new Merchants;
?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

	<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
		<div class="form-group">
			<?php echo $form->labelEx($model,'Seleziona il Commerciante'); ?>
			<?php echo $form->dropDownList($model,'id_merchant',$listaMerchants,array("disabled" => $disabled,'class'=>'form-control'));	?>
		</div>
	<?php } ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'denomination'); ?>
		<?php echo $form->textField($model,'denomination',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
		<p class="text-primary"><i>Indicare la denominazione del negozio</i></p>
		<?php echo $form->error($model,'denomination',array('class'=>'alert alert-danger')); ?>
	</div>

	<?php
	$display = ($model->address != '' ? '' : 'none');
	if (Yii::app()->user->objUser['privilegi'] < 20)
		$display = '';

	?>
	<div class='hiddenSameAddress' style="display:<?php echo $display;?>">
		<div class="form-group" id='sameAddressSelect'>
			<?php echo $form->labelEx($model,'Vuoi utilizzare questo indirizzo?'); ?>
			<select class="form-control" id="Stores_stesso_indirizzo">
				<option value='1'>Si</option>
				<option value='0'>No</option>
			</select>
			<p class="text-primary"><i>Se la sede del negozio corrisponde all'indirizzo utilizzato in fase di registrazione, scegli '<b>SI</b>'</i></p>

		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'vat'); ?>
			<?php echo $form->textField($model,'vat',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'vat',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'address'); ?>
			<?php echo $form->textField($model,'address',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'address',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'cap'); ?>
			<?php echo $form->textField($model,'cap',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'cap',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'provincia'); ?>
			<?php echo $form->dropDownList($model,'provincia',$listaProvince,array('class'=>'form-control','options'=>[$optionProvince=>['selected'=>true]]));	?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'city'); ?>
			<?php echo $form->dropDownList($model,'city',$listaComuni,array('class'=>'form-control'));	?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'county'); ?>
			<?php echo $form->dropDownList($model,'county',$countryData,array('class'=>'form-control'));	?>
		</div>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
