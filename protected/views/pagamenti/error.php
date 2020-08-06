<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	'Error',
);
?>

<h2>Errore </h2>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>
