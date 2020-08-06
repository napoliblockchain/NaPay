<?php
/* @var $this StoresController */
/* @var $dataProvider CActiveDataProvider */


#echo '<pre>'.print_r($dataProvider,true).'</pre>';
#exit;
$posURL = Yii::app()->createUrl('pos/create');

$posizioneMenu = (WebApp::isMobileDevice() ? 'in alto a destra' : 'alla tua sinistra');

// se sei admin visualizzi
$visible = (Yii::app()->user->objUser['privilegi'] == 20) ? true : false;


// sei admin, ma la carica di tesoriere non può vedere...
$users = Users::model()->findByPk(Yii::app()->user->objUser['id_user']);
if ($users->id_carica == 4)
	$visible = false;


$actionURL = Yii::app()->createUrl('stores/create');
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>

		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-shopping-cart"></i>
						<span class="card-title">Lista negozi</span>
						<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
							<div class="float-right">
								<a href="<?php echo $actionURL;?>">
									<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
										<i class="fa fa-plus"></i></button>
								</a>
							</div>
						<?php } ?>

					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">
							<?php
							$this->widget('zii.widgets.grid.CGridView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
								//'htmlOptions' => array('class' => 'table table-borderless text-dark table-data4 table-wallet'),
								'htmlOptions' => array('class' => 'table table-striped text-dark table-data4 table-wallet'),
							    'dataProvider'=>$dataProvider,
								'columns' => array(
									array(
							            'name'=>'id_merchant',
										// 'value'=> '(Merchants::model()->findByPk($data->id_merchant)) === null)
										// 				? "non trovato" :
										// 				CHtml::link(
										// 					CHtml::encode(
										// 						Merchants::model()->findByPk($data->id_merchant)->denomination),
										// 						Yii::app()->createUrl("stores/view")."&id=".CHtml::encode(
										// 							crypt::Encrypt($data->id_store)
										// 						)
										// 					)
										// 				)',
										'visible' => $visible,
										'value' => '(Merchants::model()->findByPk($data->id_merchant) === null) ? "" : CHtml::link(
														Merchants::model()->findByPk($data->id_merchant)->denomination,
														Yii::app()->createUrl("stores/view")."&id=".crypt::Encrypt($data->id_store)

													)',
										'type'=>'raw',
							        ),
									array(
							            'name'=>'denomination',
										'type'=>'raw',
										'value' => '(Merchants::model()->findByPk($data->id_merchant) === null) ? $data->denomination
											: CHtml::link(CHtml::encode($data->denomination), Yii::app()->createUrl("stores/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_store)))',
							        ),
									array(
							            'name'=>'bps_storeid',
										'header'=>'ID Negozio',
							            'value'=>'$data->bps_storeid',
										'visible' => $visible,
							        ),
									array(
										'name'=>'',
										'value'=> '',
									)
								)
							));
							?>
							<?php
								$stores = $dataProvider->getData();
								if (Yii::app()->user->objUser['privilegi'] == 10) {
									if (count($stores)==0){
										include (Yii::app()->basePath.'/views/no-store.php');
									}else if (count($pos) == 0){
										include (Yii::app()->basePath.'/views/no-pos.php');
									}//endif $pos
								}//endif privilegi == 10
						 	?>
						</div>
					</div>
				</div>
			</div>

		</div>

		<?php echo Logo::footer(); ?>
	</div>
</div>
