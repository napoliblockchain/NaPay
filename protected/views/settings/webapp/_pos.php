<?php
$viewName = 'POS';
$visible = false;

if ($model->pos_pairingCode <> '' || $model->pos_sin <> '') {
	$this->renderPartial('webapp/pos/view', array('model'=>$model));
}else{
	$this->renderPartial('webapp/pos/create', array('model'=>$model));
} ?>
