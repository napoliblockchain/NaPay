<?php
/* @var $this MerchantsController */
/* @var $model Merchants */
/* @var $model Users */
/* @var $form CActiveForm */

$ajaxSelectbyCap = Yii::app()->createUrl('backend/selectComuneByCap');
$ajaxSelectProvince = Yii::app()->createUrl('backend/ajaxSelectComune');

if (empty($preferredCoinList))
	$preferredCoinList = [];

if ($settings->blockchainAsset == '')
	$settings->blockchainAsset = "{'BTC':'BTC'}";

$enabledAssets = CJSON::decode($settings->blockchainAsset);

$listaServerBlockchain = CHtml::listData(Blockchains::model()->findAll(), 'url', 'denomination');

$myScript = <<<JS
    var ajax_loader_url = 'css/images/loading.gif';

	var formName = 'Merchants'; //da cambiare in base al Form di appartenenza
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

JS;
Yii::app()->clientScript->registerScript('myScript', $myScript);

?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'merchants-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));


//carico i paesi
$countryData = WebApp::CountryDataset();

if (!isset($_POST['Merchants']['county']))
    $model->county = 'IT';

//CARICO PROVINCE E COMUNI
if ($model->isNewRecord){
	// create
	$listaProvince = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'sigla ASC')), 'sigla', function($descrizione) {
        return $descrizione->sigla.' ('.$descrizione->provincia.')';
	});
	//lista comuni italiani viene creata dinamicamente alla scelta della provincia
	$listaComuni = array();
	$optionProvince = "";
}else{
	//update
	$listaProvince = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'sigla ASC')), 'sigla', function($descrizione) {
       return $descrizione->sigla.' ('.$descrizione->provincia.')';
	});
	$listaComuni = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'citta ASC')), 'id_comune', function($descrizione) {
        return $descrizione->citta.' ('.$descrizione->sigla.')';
	});
	$optionProvince = ComuniItaliani::model()->findByPk($model->city)->sigla;
}
$listaProvince[]=' ';
asort($listaProvince);

//carico la lista associazioni
// $criteria=new CDbCriteria();
// $criteria->compare('deleted',0,false);
// $associations=Associations::model()->findAll($criteria);
//
// $listaAssociations = CHtml::listData( $associations, 'id_association' , 'denomination');
// if (!(isset($listaAssociations)))
//     $listaAssociations[] = 'Nessuna Associazione di categoria';
//
// ksort($listaAssociations);

//carico la lista gateways
$gateways=Gateways::model()->findAll();
$listaGateways = CHtml::listData( $gateways, 'id_gateway' , 'denomination');


$disabled = 'disabled';
if ($model->isNewRecord){
    $disabled = '';
    if (!isset($users->id_user)){
        $iterator = new CDataProviderIterator($users);
        foreach($iterator as $item) {
            $listaUtenti[$item->id_user]=$item->surname . chr(32). $item->name;
        }
    }

}
if ($disabled == 'disabled'){
	$listaUtenti = CHtml::listData(Users::model()->findAll(), 'id_user', function($utenti) {
	     return CHtml::encode($utenti->surname . chr(32). $utenti->name);
	});
}


if (!(isset($listaUtenti)))
	$listaUtenti = [];
#echo "<pre>".print_r($users,true)."</pre>";
#exit;

?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>


			<?php if (Yii::app()->user->objUser['privilegi'] == 20 ){ ?>
				<div class="alert alert-secondary">
                    <div class="form-group">
    					<div class="form-group">
    						<?php echo $form->labelEx($model,'Seleziona l\'Utente/Socio'); ?>
    						<?php echo $form->dropDownList($model,'id_user',$listaUtenti,array("disabled" => $disabled,'class'=>'form-control'));	?>
    					</div>
    				</div>
                    <div class="form-group">
                			<?php echo $form->labelEx($settings,'Seleziona il Server Blockchain'); ?>
                			<?php echo $form->dropDownList($settings,'blockchainAddress',$listaServerBlockchain,array('class'=>'form-control'));	?>
                			<?php echo $form->error($settings,'blockchainAddress',array('class'=>'alert alert-danger')); ?>
                	</div>

                	<div class="row form-group ">
                		<div class="col col-md-3">
                			<label class=" form-control-label"><?php echo $form->labelEx($settings,'blockchainAsset'); ?></label>
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
                </div>
			<?php }else{
				$model->id_user = Yii::app()->user->objUser['id_user'];
				echo $form->hiddenField($model,'id_user'); ?>
			<?php } ?>


	<div class="form-group">
		<?php echo $form->labelEx($model,'denomination'); ?>
		<?php echo $form->textField($model,'denomination',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'denomination',array('class'=>'alert alert-danger')); ?>
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
		<?php echo $form->dropDownList($model,'city',$listaComuni,array('class'=>'form-control','disabled'=>($model->isNewRecord ? 'disabled' : '')));	?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'county'); ?>
		<?php echo $form->dropDownList($model,'county',$countryData,array('class'=>'form-control'));	?>
	</div>




	<?php if ($model->isNewRecord && Yii::app()->user->objUser['privilegi'] == 20){	?>
		<div class="form-group">
            <?php echo "<input name='Merchants[send_mail]' type='checkbox' />"; ?>
            <label for="Merchants_send_mail" class="required">
                Invia comunicazione tramite email dell'avvenuto inserimento della nuova utenza di commerciante.
            </label>

		</div>

	<?php } ?>

	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
