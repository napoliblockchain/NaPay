<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */
?>
<style>
	.modal-backdrop {
		display: contents;
	}
</style>

<div class="form">

	<?php
	$this->pageTitle = Yii::app()->name . ' - Register';
	$form = $this->beginWidget('CActiveForm', array(
		'id' => 'register-form',
		'enableClientValidation' => false,
		// 'clientOptions'=>array(
		// 	'validateOnSubmit'=>true,
		// ),
	));

	#echo "<pre>".print_r($_POST,true)."</pre>";


	$administrator = 'Socio';
	$settings = Settings::load();
	if ($settings->version == '0000 0000')
		$administrator = "Amministratore";

	$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;


	//array corporate
	$listCorporate = [0 => 'No', 1 => 'Si'];

	//carico i paesi
	$countryData = WebApp::CountryDataset();

	//$provinceData = Utils::ProvinceDataset();

	if (!isset($_POST['UsersRegisterForm']['country']))
		$model->country = 'IT';


	//CARICO PROVINCE E COMUNI
	$listaProvince = CHtml::listData(ComuniItaliani::model()->findAll(array('order' => 'sigla ASC')), 'sigla', function ($descrizione) {
		return $descrizione->sigla . ' (' . $descrizione->provincia . ')';
	});
	$listaProvince[] = ' ';
	asort($listaProvince);

	$listaComuni = CHtml::listData(ComuniItaliani::model()->findAll(array('order' => 'citta ASC')), 'id_comune', function ($descrizione) {
		return $descrizione->citta . ' (' . $descrizione->sigla . ')';
	});

	$optionProvince = "";
	if (isset($model->city) && $model->city > 0) {
		$optionProvince = ComuniItaliani::model()->findByPk($model->city)->sigla;
		$criteria = new CDbCriteria();
		$criteria->compare('sigla', $optionProvince, false);
		$listaComuni = CHtml::listData(ComuniItaliani::model()->findAll($criteria, array('order' => 'citta ASC')), 'id_comune', function ($descrizione) {
			return $descrizione->citta . ' (' . $descrizione->sigla . ')';
		});
	}
	$model->repeat_password = $model->password;
	//campo nascosto se non corporate
	$visible_field = 'none';
	$disabled = 'disabled';
	if (isset($_POST['UsersRegisterForm']['corporate']) && $_POST['UsersRegisterForm']['corporate'] == 1) {
		$visible_field = 'ihnerit';
		$disabled = '';
		$model->denomination = $_POST['UsersRegisterForm']['denomination'];
	}




	$ajaxSelectbyCap = Yii::app()->createUrl('backend/selectComuneByCap');
	$ajaxSelectProvince = Yii::app()->createUrl('backend/ajaxSelectComune');
	$script = <<<JS
    var formName = 'UsersRegisterForm'; //da cambiare in base al Form di appartenenza
    var cap = document.querySelector('#'+formName+'_cap');
    var provincia = document.querySelector('#'+formName+'_provincia');
    var corporate = document.querySelector('#'+formName+'_corporate');
    var checkpassword = document.querySelector('#submit_button');

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



    corporate.addEventListener('change', function(){
		$('.persona_giuridica').toggle();
    });

    //verifico password wallet immessa
    checkpassword.addEventListener('click', function(e){
        e.preventDefault();

		var password = $('#UsersRegisterForm_password').val();
        if (password.length <8) {
    		document.getElementById("password_em_").style.color = 'red';
    		$('#password_em_').text('Password troppo corta! Utilizzare almeno 8 caratteri.');
            $("#UsersRegisterForm_password").focus();
    		return ;
    	}
        $('form').submit();
    });

JS;
	Yii::app()->clientScript->registerScript('script', $script);
	?>

	<div class="login-wrap">
		<div class="login-content">
			<div class="how-section1 mb-3">
				<div class="row">
					<div class="col-md-6 how-img">
						<?php Logo::login(); ?>
					</div>
					<div class="col-md-6 text-center mt-3">
						<h4 class="subheading">
							Registrazione <?php echo $administrator; ?>
						</h4>

					</div>
				</div>
			</div>

			
			<div class="login-form">
				<div class="card border border-primary">
					<div class="card-header">
						<strong class="card-title">Dati Account</strong>
					</div>
					<div class="card-body">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Email</div>
								<?php echo $form->textField($model, 'email', array('placeholder' => 'Email', 'class' => 'form-control')); ?>
							</div>
							<?php echo $form->error($model, 'email', array('class' => 'alert alert-danger')); ?>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Nome</div>
								<?php echo $form->textField($model, 'name', array('placeholder' => 'Nome', 'class' => 'form-control')); ?>
							</div>
							<?php echo $form->error($model, 'name', array('class' => 'alert alert-danger')); ?>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Cognome</div>
								<?php echo $form->textField($model, 'surname', array('placeholder' => 'Cognome', 'class' => 'form-control')); ?>

							</div>
							<?php echo $form->error($model, 'surname', array('class' => 'alert alert-danger')); ?>
						</div>

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Password</div>
								<?php echo $form->passwordField($model, 'password', array('placeholder' => 'Password', 'class' => 'form-control', 'onkeyup' => "validatePassword(this.value,'password_em_')")); ?>

							</div>
							<div class="invalid-feedback" id="password_em_"></div>
							<?php echo $form->error($model, 'password', array('class' => 'alert alert-danger')); ?>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Ripeti Password</div>
								<?php echo $form->passwordField($model, 'repeat_password', array('placeholder' => 'Ripeti Password', 'class' => 'form-control')); ?>
							</div>
							<?php //echo $form->error($model,'passwordRepeat',array('class'=>'alert alert-danger')); 
							?>
						</div>
					</div>
				</div>
				<div class="card border border-warning">
					<div class="card-header">
						<strong class="card-title">Dati Personali</strong>
					</div>
					<div class="card-body">
						<div class="form-group">
							<?php echo $form->labelEx($model, 'Persona Giuridica'); ?>
							<?php echo $form->dropDownList($model, 'corporate', $listCorporate, array('class' => 'form-control'));	?>
						</div>
						<div class="form-group persona_giuridica" style="display:<?php echo $visible_field; ?>;" id="FormDenomination">
							<?php echo $form->label($model, 'denomination'); ?>
							<?php echo $form->textField($model, 'denomination', array('size' => 60, 'maxlength' => 250, 'class' => 'form-control')); ?>
							<?php echo $form->error($model, 'denomination', array('class' => 'alert alert-danger')); ?>
						</div>

						<div class="form-group">
							<?php echo $form->label($model, "vat"); ?>
							<?php echo $form->textField($model, 'vat', array('size' => 60, 'maxlength' => 250, 'class' => 'form-control')); ?>
							<?php echo $form->error($model, 'vat', array('class' => 'alert alert-danger')); ?>
						</div>
						<div class="form-group">
							<?php echo $form->label($model, 'address'); ?>
							<?php echo $form->textField($model, 'address', array('size' => 60, 'maxlength' => 250, 'class' => 'form-control')); ?>
							<?php echo $form->error($model, 'address', array('class' => 'alert alert-danger')); ?>
						</div>

						<div class="form-group">
							<?php echo $form->label($model, 'cap'); ?>
							<?php echo $form->textField($model, 'cap', array('size' => 60, 'maxlength' => 250, 'class' => 'form-control')); ?>
							<?php echo $form->error($model, 'cap', array('class' => 'alert alert-danger')); ?>
						</div>

						<div class="form-group">
							<?php echo $form->labelEx($model, 'provincia'); ?>
							<?php echo $form->dropDownList($model, 'provincia', $listaProvince, array('class' => 'form-control', 'options' => [$optionProvince => ['selected' => true]]));	?>
						</div>

						<div class="form-group">
							<?php echo $form->labelEx($model, 'city'); ?>
							<?php //echo $form->textField($model,'city',array('size'=>60,'maxlength'=>250,'class'=>'form-control','disabled'=>'disabled')); 
							?>
							<?php echo $form->dropDownList($model, 'city', $listaComuni, array('class' => 'form-control', 'disabled' => $disabled));	?>
						</div>

						<div class="form-group">
							<?php echo $form->labelEx($model, 'country'); ?>
							<?php echo $form->dropDownList($model, 'country', $countryData, array('class' => 'form-control'));	?>
						</div>

						<div class="form-group">
							<?php echo $form->label($model, 'telefono'); ?>
							<?php echo $form->textField($model, 'telefono', array('size' => 60, 'maxlength' => 100, 'class' => 'form-control')); ?>
							<?php echo $form->error($model, 'telefono', array('class' => 'alert alert-danger')); ?>
						</div>
					</div>
				</div>


				<!-- STATUTO -->
				<div class="form-group">
					<p><?php echo $form->checkBox($model, 'consenso_statuto'); ?><i>&nbsp;Ho letto lo <a href="https://napoliblockchain.it/chi-siamo/statuto-associazione-napoli-blockchain-ets/" target="_blank">STATUTO</a> e ne condivido ogni punto.</i></p>
				</div>
				<?php echo $form->error($model, 'consenso_statuto', array('class' => 'alert alert-danger')); ?>

				<!-- PRIVACY -->
				<div class="form-group">
					<p><?php echo $form->checkBox($model, 'consenso_privacy'); ?><i>&nbsp;Ho letto l'<a href="<?php echo Yii::app()->createUrl('site/printPrivacy'); ?>" target="_blank">INFORMATIVA SULLA PRIVACY</a> e autorizzo al trattamento dei miei dati personali.</i></p>
				</div>
				<?php echo $form->error($model, 'consenso_privacy', array('class' => 'alert alert-danger')); ?>

				<!-- TERMINI -->
				<div class="input-group">
					<p><?php echo $form->checkBox($model, 'consenso_termini'); ?><i>&nbsp;Accetto le <a href="<?php echo Yii::app()->createUrl('site/termOfUse'); ?>" target="_blank">Condizioni di utilizzo</a> del software.</i></p>
				</div>
				<?php echo $form->error($model, 'consenso_termini', array('class' => 'alert alert-danger')); ?>

				<!-- TERMINI UTILIZZO POS -->
				<div class="input-group persona_giuridica" style="display:<?php echo $visible_field; ?>;">
					<p><?php echo $form->checkBox($model, 'consenso_pos'); ?><i>&nbsp;Accetto i <a href="<?php echo Yii::app()->createUrl('site/termOfUsePos'); ?>" target="_blank">Termini e le Condizioni di utilizzo</a> del POS.</i></p>
				</div>
				<?php echo $form->error($model, 'consenso_pos', array('class' => 'alert alert-danger')); ?>

				<!-- TERMINI -->
				<div class="input-group">
					<p><?php echo $form->checkBox($model, 'consenso_marketing'); ?><i>&nbsp;Acconsento al trattamento dei dati per finalità di marketing.</i></p>
				</div>
				<?php echo $form->error($model, 'consenso_marketing', array('class' => 'alert alert-danger')); ?>


				<div class="form-group">
					<?php
					$form->widget(
						'application.extensions.reCaptcha2.SReCaptcha',
						array(
							'name' => 'reCaptcha', //is requred
							'siteKey' => $reCaptcha2PublicKey, //is requred
							'model' => $form,
							'lang' => 'it-IT',
							//'theme'=>'light',
							//'size'=>'compact',
							//'attribute' => 'reCaptcha' //if we use model name equal attribute or customize attribute
						)
					);

					?>
					<?php echo $form->error($model, 'reCaptcha', array('class' => 'alert alert-danger')); ?>
				</div>


				<?php echo CHtml::submitButton('Iscriviti', array('class' => 'au-btn au-btn--block au-btn--green m-b-20', 'id' => 'submit_button')); ?>


			</div>
			<div class="bg-secondary">
				<h5 style="text-align:center; padding-top:20px;">I nostri supporter</h5>

				<div class="row">
					<div class="col" style="text-align:center;">
						<img class='login-sponsor' src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/logocomune.png" alt="">
					</div>
					<div class="col" style="text-align:center;">
						<img class='login-sponsor' width="100" height="100" src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/parthenope.png" alt="" sizes="(max-width: 150px) 100vw, 150px">
					</div>
				</div>

				<?php echo Logo::footer(); ?>
			</div>
		</div>


	</div>

	<?php $this->endWidget(); ?>
</div><!-- form -->