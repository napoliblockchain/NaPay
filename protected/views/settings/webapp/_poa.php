

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settingsPoa-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));
$model->poa_sealerPrvKey = crypt::Decrypt($model->poa_sealerPrvKey);
?>
<div class="col-md-12">
	<div class="au-card  au-card--no-pad bg-overlay--semitransparent">
		<div class="card-body ">
			<!-- <div class="form-group">
				<?php //echo $form->labelEx($model,'poa_url'); ?>
				<?php //echo $form->textField($model,'poa_url',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php //echo $form->error($model,'poa_url',array('class'=>'alert alert-danger')); ?>
			</div> -->

			<!-- <div class="form-group">
				<?php //echo $form->labelEx($model,'poa_port'); ?>
				<?php //echo $form->textField($model,'poa_port',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php //echo $form->error($model,'poa_port',array('class'=>'alert alert-danger')); ?>
			</div> -->

			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_chainId'); ?>
				<?php echo $form->textField($model,'poa_chainId',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_chainId',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_blockexplorer'); ?>
				<?php echo $form->textField($model,'poa_blockexplorer',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_blockexplorer',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_contractAddress'); ?>
				<?php echo $form->textField($model,'poa_contractAddress',array('size'=>60,'maxlength'=>250,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_contractAddress',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_abi'); ?>
				<?php echo $form->textArea($model,'poa_abi',array('rows'=>6, 'cols'=>50,'size'=>15000,'maxlength'=>15000,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_abi',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_bytecode'); ?>
				<?php echo $form->textArea($model,'poa_bytecode',array('rows'=>6, 'cols'=>50,'size'=>15000,'maxlength'=>15000,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_bytecode',array('class'=>'alert alert-danger')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_expiration'); ?>
				<?php echo $form->textField($model,'poa_expiration',array('size'=>10,'maxlength'=>10,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_expiration',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_sealerAccount'); ?>
				<?php echo $form->textField($model,'poa_sealerAccount',array('size'=>100,'maxlength'=>100,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_sealerAccount',array('class'=>'alert alert-danger')); ?>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'poa_sealerPrvKey'); ?>
				<?php echo $form->passwordField($model,'poa_sealerPrvKey',array('size'=>60,'maxlength'=>500,'class'=>'form-control')); ?>
				<?php echo $form->error($model,'poa_sealerPrvKey',array('class'=>'alert alert-danger')); ?>
			</div>
		</div>
	</div>
</div>
<?php //echo $form->hiddenField($model,'step',array('value'=>5)); ?>
<div class="col-md-12">
	<div class="form-group">
		<br>
		<?php echo CHtml::submitButton('Conferma', array('class' => 'btn btn-primary')); ?>
	</div>
</div>



<?php $this->endWidget(); ?>

</div><!-- form -->
