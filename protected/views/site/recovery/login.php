<?php
$this->pageTitle=Yii::app()->name . ' - Recupero password';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'recoverypassword-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
$settings = Settings::load();
$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;
?>

<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="form-group">
			<h3>
				<center>Recupero password</center>
			</h3>
		</div>

		<div class="login-form">
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						<img style="height:25px;" src="css/images/ic_account_circle.svg">
					</div>
					<?php echo $form->textField($model,'username',array('placeholder'=>'Email address','class'=>'form-control','style'=>'height:45px;')); ?>
				</div>
				<?php echo $form->error($model,'username',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php
				$form->widget('application.extensions.reCaptcha2.SReCaptcha', array(
						'name' => 'reCaptcha', //is requred
						'siteKey' => $reCaptcha2PublicKey, //is requred
						'model' => $form,
						'lang' => 'it-IT',
						//'attribute' => 'reCaptcha' //if we use model name equal attribute or customize attribute
					)
				); ?>
			</div>
			<?php echo CHtml::submitButton('Richiedi', array('class' => 'au-btn au-btn--block au-btn--blue m-b-20','id'=>'accedi-button')); ?>



	</div>
	<div class="bg-secondary">
	  <h5 style="text-align:center; padding-top:20px;">I nostri supporter</h5>

	  <div class="row">
	      <div class="col" style="text-align:center;">
	          <img class='login-sponsor' src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/logocomune.png" alt="" >
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
