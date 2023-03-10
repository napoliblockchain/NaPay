<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle = Yii::app()->name . ' - Login';
$form = $this->beginWidget('CActiveForm', array(
	'id' => 'login-form',
	'enableClientValidation' => false,
	'clientOptions' => array(
		'validateOnSubmit' => true,
	),
));
$settings = Settings::load();
$reCaptcha2PublicKey = $settings->reCaptcha2PublicKey;
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
						Versamento Quota Associativa
					</h4>
					
				</div>
			</div>
		</div>



		

		<div class="login-form">
			<div class="form-group">
				<!-- <label>Email Address</label> -->
				<div class="input-group">
					<div class="input-group-addon">
						<!-- <i class="fa fa-envelope"></i> -->
						<img style="height:25px;" src="css/images/ic_account_circle.svg">
					</div>
					<?php echo $form->textField($model, 'username', array('placeholder' => 'Email address', 'class' => 'form-control', 'style' => 'height:45px;')); ?>

				</div>
				<?php echo $form->error($model, 'username', array('class' => 'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<!-- <label>Password</label> -->
				<div class="input-group">
					<div class="input-group-addon">
						<!-- <i class="fa fa-asterisk"></i> -->
						<img style="height:25px;" src="css/images/ic_vpn_key.svg">
					</div>
					<?php echo $form->passwordField($model, 'password', array('placeholder' => 'Password', 'class' => 'form-control', 'style' => 'height:45px;')); ?>

				</div>
				<?php echo $form->error($model, 'password', array('class' => 'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php
				$form->widget(
					'application.extensions.reCaptcha2.SReCaptcha',
					array(
						'name' => 'reCaptcha', //is requred
						'siteKey' => $reCaptcha2PublicKey, //is requred
						'model' => $form,
						'lang' => 'it-IT',
						//'attribute' => 'reCaptcha' //if we use model name equal attribute or customize attribute
					)
				);

				?>
				<?php echo $form->error($model, 'reCaptcha', array('class' => 'alert alert-danger')); ?>


			</div>



			<?php echo CHtml::submitButton('Accedi', array('class' => 'au-btn au-btn--block au-btn--blue m-b-20', 'id' => 'accedi-button')); ?>


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