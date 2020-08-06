<?php
$this->pageTitle=Yii::app()->name . ' - Imposta password';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'passwords-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
?>

<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="form-group">
			<strong>
				<center>Inserisci la nuova password</center>
			</strong>
		</div>

		<div class="login-form">
				<div class="form-group">
					<label>Password</label>
					<div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-asterisk"></i>
                        </div>
						<?php echo $form->passwordField($model,'new_password',array('placeholder'=>'Password','class'=>'form-control')); ?>

					</div>
					<?php echo $form->error($model,'new_password',array('class'=>'alert alert-danger')); ?>
				</div>
				<div class="form-group">
					<label>Ripeti Password</label>
					<div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-asterisk"></i>
                        </div>
						<?php echo $form->passwordField($model,'new_password_confirm',array('placeholder'=>'Ripeti Password','class'=>'form-control')); ?>

                    </div>
					<?php echo $form->error($model,'new_password_confirm',array('class'=>'alert alert-danger')); ?>
				</div>

				<?php echo CHtml::submitButton('Conferma', array('class' => 'au-btn au-btn--block au-btn--blue m-b-20')); ?>
				<div class="row">
					<div class="col" style="text-align:center;">
						<img class='login-sponsor' src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/logocomune.png" alt="" >
					</div>
					<div class="col" style="text-align:center;">
						<img class='login-sponsor' width="150" height="150" src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/parthenope.png" alt="" sizes="(max-width: 150px) 100vw, 150px">
					</div>
				</div>
				<?php echo Logo::footer(); ?>
		</div>

	</div>
</div>
<?php $this->endWidget(); ?>
