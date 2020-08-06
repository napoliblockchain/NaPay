<?php
/* @var $this shoppings */
/* @var $dataProvider CActiveDataProvider */
?>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'products-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

$actionURL = Yii::app()->createUrl('verbali/create');

(Yii::app()->user->objUser['privilegi'] == 20) ? $visible = '' : $visible = 'none;';

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>

		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-book"></i>
						<span class="card-title">Lista verbali di assemblea</span>

						<div class="float-right" style="display: <?php echo $visible; ?>">
							<a href="<?php echo $actionURL;?>">
								<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus"></i></button>
							</a>
						</div>

					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">

						<?php

						$this->widget('zii.widgets.grid.CGridView', array(
						//$this->widget('ext.selgridview.SelGridView', array(
							//'id'=>'notifications-grid',
							'selectableRows' => 2,
							//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
							//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
							'htmlOptions' => array('class' => 'table table-wallet'),
						    'dataProvider'=>$dataProvider,
							'columns' => array(
								array(
									'type'=> 'raw',
									'name'=>'id_verbali',
									'value' => 'CHtml::link($data->id_verbali)',
								),
								array(
									'type'=> 'raw',
									'name'=>'data_verbale',
									'value'=> 'CHtml::link(CHtml::encode(WebApp::data_it($data->data_verbale)),Yii::app()->createUrl("verbali/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_verbali)))',
								),
								array(
									'type'=> 'raw',
						            'name'=>'descrizione_verbale',
									'value' => 'CHtml::link($data->descrizione_verbale,Yii::app()->createUrl("verbali/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_verbali)))',
						        ),
								array(
									'name'=>'id_verbali',
									'header'=>'Download',
									'type' => 'raw',
									'value'=> 'CHtml::link("<img src=\'css/images/pdf-icon.png\' width=30 />",Yii::app()->createUrl("verbali/download",["id"=>crypt::Encrypt($data->id_verbali)]),["target"=>"_blank"])',
								),

							)
						));
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>


	</div>
</div>
<?php $this->endWidget(); ?>
</div><!-- form -->
