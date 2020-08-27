<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));

$URLRecoveryPassword = Yii::app()->createUrl('site/recoverypassword');
$URLVersaquota = Yii::app()->createUrl('site/loginquota');

$settings = Settings::load();
$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;

$iscriviti = <<<JS
	$(".button-show-items").click(function(){
		$( ".line-items" ).toggle(750);
		$( ".mostra-scelte" ).toggle(50);
		$( ".nascondi-scelte" ).toggle(50);

	});

	// chiede di installare la webapop sul desktop
	var accediButton = document.querySelector('#accedi-button');
	function openSaveOnDesktop() {
	  //createPostArea.style.display = 'block';
	  if (deferredPrompt) {
	    deferredPrompt.prompt();

	    deferredPrompt.userChoice.then(function(choiceResult) {
	      console.log(choiceResult.outcome);

	      if (choiceResult.outcome === 'dismissed') {
	        console.log('User cancelled installation');
	      } else {
	        console.log('User added to home screen');
	      }
	    });

	    deferredPrompt = null;
	  }
	}
	accediButton.addEventListener('click', openSaveOnDesktop);
JS;

Yii::app()->clientScript->registerScript('iscriviti', $iscriviti);

?>
<div class="login-wrap">

	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="form-group">
			<h3>
				<center><?php echo Yii::t('lang','Welcome to');?> <?php echo Yii::app()->params['shortName'];?></center>
			</h3>
		</div>

		<div class="login-form">
				<div class="form-group">
					<!-- <label>Email Address</label> -->
					<div class="input-group">
                        <div class="input-group-addon">
                            <!-- <i class="fa fa-envelope"></i> -->
							<img style="height:25px;" src="css/images/ic_account_circle.svg">
                        </div>
						<?php echo $form->textField($model,'username',array('placeholder'=>'Email address','class'=>'form-control','style'=>'height:45px;')); ?>

					</div>
					<?php echo $form->error($model,'username',array('class'=>'alert alert-danger')); ?>
				</div>
				<div class="form-group">
					<!-- <label>Password</label> -->
					<div class="input-group">
            <div class="input-group-addon">
							<img style="height:25px;" src="css/images/ic_vpn_key.svg">
            </div>
						<?php echo $form->passwordField($model,'password',array('placeholder'=>'Password','class'=>'form-control','style'=>'height:45px;')); ?>
          </div>
					<div><a class="text-danger" href="<?php echo Yii::app()->createUrl('site/recoverypassword'); ?>">Reset password</a></div>
					<?php echo $form->error($model,'password',array('class'=>'alert alert-danger')); ?>
				</div>
				<div class="form-group">
					<?php
					$form->widget('application.extensions.reCaptcha2.SReCaptcha', array(
	        		'name' => 'reCaptcha', //is requred
	        		'siteKey' => $reCaptcha2PublicKey, //Yii::app()->params['reCaptcha2PublicKey'], //is requred
	        		'model' => $form,
							'lang' => 'it-IT',
							// 'theme'=>'light',
							// 'size'=>'compact',
	        		//'attribute' => 'reCaptcha' //if we use model name equal attribute or customize attribute
						)
					);
					?>
					<?php echo $form->error($model,'reCaptcha',array('class'=>'alert alert-danger')); ?>
				</div>


				<?php echo CHtml::submitButton('Accedi', array('class' => 'au-btn au-btn--block au-btn--blue m-b-20','id'=>'accedi-button')); ?>
				<div class="form-group">
					<div class="row">
						<div class="col-lg-12">

						<center>
								<a href="<?php echo Yii::app()->createUrl('site/register'); ?>">
								<text class="text text-dark">
									Clicca qui per iscriverti
								</text>
							</a>
						</center>

						</div>



					</div>
				</div>






		</div>
		<?php echo Logo::footer('#333'); ?>
	</div>
</div>
<?php $this->endWidget(); ?>
