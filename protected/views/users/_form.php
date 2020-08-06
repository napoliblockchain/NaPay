<?php
//lista cariche
$cariche=Cariche::model()->findAll();
$listaCariche = CHtml::listData( $cariche, 'id_carica' , 'description');
$disabled = 'disabled';
if (($model->isNewRecord) || Yii::app()->user->objUser['privilegi'] == 20)
	$disabled = '';


//carico i paesi
$countryData = WebApp::CountryDataset();

//lista comuni italiani
$listaComuni = CHtml::listData(ComuniItaliani::model()->findAll(array('order'=>'citta ASC')), 'id_comune', function($descrizione) {
    return CHtml::encode(str_replace("'","`",$descrizione->citta).' ('.$descrizione->sigla.')');
});

$listCorporate = [0=>'No',1=>'Si'];
$script = <<<JS
	$("#Users_corporate").change(function() {
		var corporate = this.value;
		if (corporate==1)
			$('#FormDenomination').show();
		else
			$('#FormDenomination').hide();
	});

JS;
Yii::app()->clientScript->registerScript('script', $script);

$userSettings = Settings::loadUser($model->id_user);
(isset($userSettings->telefono) ? $model->telefono = $userSettings->telefono : $model->telefono = '+39 ')

?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
)); ?>
<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>

<div class="card border border-primary">
	<div class="card-header">
		<strong class="card-title">Dati Account</strong>
	</div>
	<div class="card-body">
		<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
		<i>
			I soci inseriti manualmente e che hanno la carica di Presidente, Vice Presidente, Tesoriere, Segretario, saranno generati con la funzione
			di Amministratore.<br>
			Le rimanenti categorie di utenti inseriti avranno lo status di socio.<br>
			<!-- Pertanto, nessuno sarà inserito con funzionalità di Commerciante, a prescindere dalla forma giuridica. -->
			<br><br>
		</i>
		<?php } ?>

		<div class="form-group">
			<?php echo $form->labelEx($model,'Seleziona la carica'); ?>
			<?php echo $form->dropDownList($model,'id_carica',$listaCariche,array("disabled" => $disabled,'class'=>'form-control'));	?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'email'); ?>
			<?php echo $form->textField($model,'email',array('size'=>100,'readonly'=>!$model->isNewRecord,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'email',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'name'); ?>
			<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'name',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'surname'); ?>
			<?php echo $form->textField($model,'surname',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'surname',array('class'=>'alert alert-danger')); ?>
		</div>

		<?php if ($model->isNewRecord){ ?>
			<div class="form-group">
				<?php $model->password = Utils::passwordGenerator(); ?>
				<?php echo $form->labelEx($model,'password'); ?>
				<?php echo $form->textField($model,'password',array('size'=>60,'maxlength'=>250,'placeholder'=>'Password','class'=>'form-control')); ?>
				<?php echo $form->error($model,'password',array('class'=>'alert alert-light')); ?>
			</div>


			<?php //echo $form->hiddenField($model,'id_users_type',array('value'=>3)); ?>
			<?php echo $form->hiddenField($model,'activation_code',array('value'=>md5($model->password))); ?>
			<?php echo $form->hiddenField($model,'status_activation_code',array('value'=>0)); ?>

			<div class="form-group">
				<p><i>Salvare la password nel caso in cui non verrà inviata la mail.</i></p>

				<?php echo $form->labelEx($model,'Invia email'); ?>
				<?php echo $form->checkBox($model,'send_mail'); ?>
			</div>
		<?php }else{ ?>

			<?php //echo $form->hiddenField($model,'id_users_type'); ?>
			<?php //echo $form->hiddenField($model,'status'); ?>
			<?php echo $form->hiddenField($model,'password'); ?>
			<?php echo $form->hiddenField($model,'activation_code'); ?>
			<?php echo $form->hiddenField($model,'status_activation_code'); ?>
		<?php } ?>
	</div>
</div>
<div class="card border border-warning">
	<div class="card-header">
		<strong class="card-title">Dati Personali</strong>
	</div>
	<div class="card-body">
		<div class="form-group">
			<?php echo $form->labelEx($model,'Persona Giuridica'); ?>
			<?php echo $form->dropDownList($model,'corporate',$listCorporate,array("disabled" => $disabled,'class'=>'form-control'));	?>
		</div>
		<div class="form-group" style="display:<?php echo ($model->corporate == 1 ? '':'none;'); ?>" id="FormDenomination">
			<?php echo $form->label($model,'denomination'); ?>
			<?php echo $form->textField($model,'denomination',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'denomination',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->label($model,"vat"); ?>
			<?php echo $form->textField($model,'vat',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'vat',array('class'=>'alert alert-danger')); ?>
		</div>
		<div class="form-group">
			<?php echo $form->label($model,'address'); ?>
			<?php echo $form->textField($model,'address',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'address',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->label($model,'cap'); ?>
			<?php echo $form->textField($model,'cap',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'cap',array('class'=>'alert alert-danger')); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'city'); ?>
			<?php echo $form->dropDownList($model,'city',$listaComuni,array('class'=>'form-control'));	?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'country'); ?>
			<?php echo $form->dropDownList($model,'country',$countryData,array('class'=>'form-control'));	?>
		</div>
		<div class="form-group">
			<?php echo $form->label($model,'telefono'); ?>
			<?php echo $form->textField($model,'telefono',array('size'=>60,'maxlength'=>100,'class'=>'form-control')); ?>
			<?php echo $form->error($model,'telefono',array('class'=>'alert alert-danger')); ?>
		</div>
	</div>
</div>



	<div class="form-group">
		<?php echo CHtml::submitButton(($model->isNewRecord ? 'Inserisci' : 'Salva'), array('class' => 'btn btn-primary')); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->
