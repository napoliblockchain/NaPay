<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'users-form',
	'enableAjaxValidation'=>false,
)); ?>
<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="form-group">
		<?php echo $form->hiddenField($model,'ga_secret_key',array('value'=>$secret)); ?>
	</div>

	<div class="col typo-articles">
		<h4>Per utilizzare un'applicazione di autenticazione a 2 fattori segui i passaggi seguenti:</h4>
		<p>
			<ol class="vue-ordered"  style="padding-left:40px;">
				<li>Scarica un'applicazione di autenticazione a 2 fattori come Microsoft Authenticator, oppure <a href="https://play.google.com/store" target="_blank">Google Authenticator</a>.</li>
				<br>
				<li>Fai la scansione del QR Code, oppure inserisci manualmente questo codice
					<span class="bg-dark text-light" style="padding: 0px 15px 0px 15px; border-radius:12px; font-size:1.2em;"><?=$secret?></span> nella tua applicazione di
					autenticazione.
					<br><br>
					<p style="text-align:center;" class="text-dark">
					   <img src="<?=$qrCodeUrl?>" />
				   </p>
				</li>
				<br>
				<li>Una volta che hai effettuato la scansione o inserito il codice manualmente, la tua applicazione di autenticazione a 2 fattori
					ti mostrerà un codice univoco. Inserisci questo codice nel box di conferma qui sotto.</li>
			</ol>
		</p>
		<br>

		<div class="form-group">
			<div class="input-group">
				<div class="input-group-addon">Codice di verifica</div>
				<?php echo $form->textField($model,'ga_cod',array('class'=>'form-control','style'=>'height:45px;')); ?>
			</div>
			<?php echo $form->error($model,'ga_cod',array('class'=>'alert alert-danger')); ?>
		</div>
		<div class="form-group">
			<?php echo CHtml::submitButton(('Conferma'), array('class' => 'btn btn-primary')); ?>
		</div>

	</div>





<?php $this->endWidget(); ?>

</div><!-- form -->
