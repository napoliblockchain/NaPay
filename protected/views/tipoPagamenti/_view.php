<?php
/* @var $this TipoPagamentiController */
/* @var $data TipoPagamenti */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_tipo_pagamento')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id_tipo_pagamento), array('view', 'id'=>$data->id_tipo_pagamento)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />


</div>