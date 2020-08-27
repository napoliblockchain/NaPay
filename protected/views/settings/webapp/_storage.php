<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsStorage-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

if (!(empty($model->fileSystemStorageKey)))
	$model->fileSystemStorageKey = crypt::Decrypt($model->fileSystemStorageKey);
?>


<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<div class="form-group">
				<p class="text-primary"><?php echo Yii::t('lang','Insert key for encrypting file system storage'); ?>
				</p>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'fileSystemStorageKey'); ?>
				<?php echo $form->passwordField($model,'fileSystemStorageKey',array('size'=>50,'maxlength'=>150,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'fileSystemStorageKey',array('class'=>'alert alert-danger')); ?>
			</div>

		</div>
	</div>
</div>
<div class="col-md-12">
	<div class="form-group">
		<br>
		<?php echo CHtml::submitButton(Yii::t('lang','Confirm'), array('class' => 'btn btn-primary')); ?>
	</div>
</div>



<?php $this->endWidget(); ?>

</div><!-- form -->
