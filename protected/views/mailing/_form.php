<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'mailing-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>true,
		'action'=>$this->createUrl('mailing/index'),
		'enableClientValidation'=>true,
));
?>
	<?php echo $form->errorSummary($model, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="form-group">
		<?php echo $form->labelEx($model,'subject'); ?>
		<?php echo $form->textField($model,'subject',array('placeholder'=>Yii::t('model','Subject'),'class'=>'form-control')); ?>
		<?php echo $form->error($model,'subject',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'data'); ?>
		<?php echo $form->dateField($model,'data',array('placeholder'=>Yii::t('model','Subject'),'class'=>'form-control')); ?>


		<?php
			// $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			// 	'id' => 'start_date'.uniqid(),
			// 	'model' => $model,
			// 	'name'=>'MailingForm[data]',
			// 	//'attribute' => 'data',
			// 	'htmlOptions' => array(
			// 		'size' => '10',			// textField size
			// 		'maxlength' => '10',	// textField maxlength
			// 		'style' => 'width: 100px',
			// 		'class'=>'form-control'
			// 	),
			// 	'options' => array(
			// 		//'numberOfMonths'=>2,
	   		// 		// 'showButtonPanel'=>true,
            //         'showAnim' => 'fadeIn', //'slide','fold','slideDown','fadeIn','blind','bounce','clip','drop'
            //         'showOtherMonths' => true, // Show Other month in jquery
            //         'selectOtherMonths' => true, // Select Other month in jquery
            //     ),
			// 	'language'=>Yii::app()->language,
			// ));
		?>
		<?php echo $form->error($model,'data',array('class'=>'alert alert-danger')); ?>
	</div>
	<div class="form-group">
		<?php echo $form->labelEx($model,'time'); ?>
		<?php echo $form->textField($model,'time',array('placeholder'=>Yii::t('model','Time'),'class'=>'form-control','style' => 'width: 100px')); ?>
		<?php echo $form->error($model,'time',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'place'); ?>
		<?php echo $form->textField($model,'place',array('placeholder'=>Yii::t('model','Place'),'size'=>500,'maxlength'=>500,'class'=>'form-control')); ?>
		<?php echo $form->error($model,'place',array('class'=>'alert alert-danger')); ?>
	</div>



	<div class="form-group">
		<?php echo $form->labelEx($model,'body'); ?>
		<?php echo $form->textArea($model,'body',array('placeholder'=>Yii::t('model','Write message'),'class'=>'form-control','rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'body',array('class'=>'alert alert-danger')); ?>
	</div>

	<div class="form-group">
		<?php echo CHtml::submitButton('Invia', array('class' => 'btn btn-primary','id'=>'btn-confirm')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
