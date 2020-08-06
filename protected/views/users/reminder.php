<?php
/* @var $this PosTrxController */
/* @var $dataProvider CActiveDataProvider */
?>
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'reminder-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fa fa-exclamation-triangle"></i>
						<span class="card-title">Lista utenti con pagamenti in scadenza</span>

					</div>
					<div class="card-body">
						<p><i>La scadenza per il pagamento dell'iscrizione è il 31 gennaio dell'anno solare in corso, oltre il quale il socio non potrà più utilizzare l'applicazione</i></p>
						<div class="table-responsive table--no-card ">

						<?php

						//$this->widget('zii.widgets.grid.CGridView', array(
						$this->widget('ext.selgridview.SelGridView', array(
							'id'=>'reminder-grid',
							'selectableRows' => 2, // 0,1,2
							//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
							//'htmlOptions' => array('class' => 'table table table-borderless table-data3'),
							//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
							'htmlOptions' => array('class' => 'table table-wallet'),
						    'dataProvider'=>$dataProvider,
							'columns' => array(
								array(
								   'id'=>'selectedReminder',
								   'class'=>'CCheckBoxColumn',
							    ),
								array(
									'type' => 'raw',
									'name'=>'name',
									'value' => 'CHtml::link(CHtml::encode(strtoupper(Users::model()->findByPk($data->id_user)->surname).chr(32).Users::model()->findByPk($data->id_user)->name), Yii::app()->createUrl("users/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_user)))',
						        ),

								array(
									'name'=>'email',
									'type'=>'raw',
									'value' => 'CHtml::link(CHtml::encode($data->email), Yii::app()->createUrl("users/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_user)))',
								),
								array(
									'name'=>'id_carica',
									'value'=>'Cariche::model()->findByPk($data->id_carica)->description',
								),
								array(
									'name'=>'id_users_type',
									'value'=>'UsersType::model()->findByPk($data->id_users_type)->desc',
								),
								array(
									'type'=> 'raw',
									'header'=>'Iscrizione',
									'name'=>'id_user',
									'value'=>'WebApp::StatoPagamenti($data->id_user)',
								),
								array(
									'type'=> 'raw',
									'name'=>'id_user',
									'header'=>'Solleciti',
									'value'=>'WebApp::ContaSollecitiPagamenti($data->id_user)',
								),

							)
						));
						?>
						</div>
					</div>
					<div class="card-footer">
						<?php if ($dataProvider->totalItemCount >0) { ?>
						<div class="form-group">
							<?php echo CHtml::submitButton('Invia promemoria', array('class' => 'btn btn-primary ')); ?>
						</div>
						<?php } ?>
					</div>
				</div>

			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
<?php $this->endWidget(); ?>
</div><!-- form -->
