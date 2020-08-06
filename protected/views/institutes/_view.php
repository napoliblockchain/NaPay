<?php
/* @var $this InstitutesController */
/* @var $data Institutes */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_institute')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id_institute), array('view', 'id'=>$data->id_institute)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('wallet_address')); ?>:</b>
	<?php echo CHtml::encode($data->wallet_address); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('max_wait_time')); ?>:</b>
	<?php echo CHtml::encode($data->max_wait_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('max_wait_message')); ?>:</b>
	<?php echo CHtml::encode($data->max_wait_message); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('default_sending_quantity')); ?>:</b>
	<?php echo CHtml::encode($data->default_sending_quantity); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('min_fund_alert')); ?>:</b>
	<?php echo CHtml::encode($data->min_fund_alert); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('email_fund_alert')); ?>:</b>
	<?php echo CHtml::encode($data->email_fund_alert); ?>
	<br />

	*/ ?>

</div>