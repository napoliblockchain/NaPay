<?php
/* @var $this ShopsController */
/* @var $model Shops */
$viewName = 'Self POS';


$deleteURL = Yii::app()->createUrl('shops/delete').'&id='.crypt::Encrypt($model->id_shop);

$general_settings = Yii::app()->createUrl('shops/general',['id'=>crypt::Encrypt($model->id_shop)]);
$prodotti = Yii::app()->createUrl('shops/products',['id'=>crypt::Encrypt($model->id_shop)]);

// include ('js_products.php');

?>
<style>
td.button-column{
	vertical-align: middle;
}
</style>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-globe"></i>
						<span class="card-title">Dettagli <?php echo $viewName; ?></span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card ">
							<?php $this->widget('zii.widgets.CDetailView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
								'data'=>$model,
								'attributes'=>array(
									array(
							            'label'=>'Negozio',
							            'value'=>isset(Stores::model()->findByAttributes(array('id_store'=>$model->id_store))->denomination) ? Stores::model()->findByAttributes(array('id_store'=>$model->id_store))->denomination : ""
							        ),
									'denomination',
									'bps_shopid',


								),
							));
							?>
						</div>
					</div>

					<div class="card-footer">
							<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelModal">Elimina</button>
					</div>
				</div>
			</div>
		</div>

		<!-- general settings -->
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header">

						<span class="card-title">Impostazioni Generali</span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card">
							<?php
							$this->widget('zii.widgets.CDetailView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
								'data'=>$model,
								'attributes'=>array(
									[	'label'=>'Titolo',
										'value'=>$shopSettings->Title
									],
									[	'label'=>'User can input custom amount',
										'value'=>$shopSettings->ShowCustomAmount
									],
									[	'label'=>'User can input discount in %',
										'value'=>$shopSettings->ShowDiscount
									],
									[	'label'=>'Enable tips',
										'value'=>$shopSettings->EnableTips
									],
									[	'label'=>'Text to display on each buttons for items with a specific price',
										'value'=>$shopSettings->ButtonText
									],
									[	'label'=>'Text to display on buttons next to the input allowing the user to enter a custom amount',
										'value'=>$shopSettings->CustomButtonText
									],

									[	'label'=>'Text to display in the tip input',
										'value'=>$shopSettings->CustomTipText
									],
									[	'label'=>'Tip percentage amounts (comma separated)',
										'value'=>$shopSettings->CustomTipPercentages
									],
									// [	'label'=>'Description',
									// 	'value'=>$shopSettings->Description
									// ],
								),
							));
							?>
						</div>
					</div>
					<div class="card-footer">
						<a href="<?php echo $general_settings;?>">
							<button class="btn btn-warning">Modifica</button>
						</a>
					</div>
				</div>
			</div>
		</div>
		<!-- prodotti -->
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<span class="card-title">Prodotti</span>
						<div class="float-right">
							<?php $actionURL = Yii::app()->createUrl('products/create',array('id_shop'=>crypt::Encrypt($model->id_shop))); ?>
							<a href="<?php echo $actionURL;?>">
								<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus"></i></button>
							</a>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card">
							<?php $this->widget('zii.widgets.grid.CGridView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
								'htmlOptions' => array('class' => 'table table-striped text-dark table-data4 table-wallet'),
							    'dataProvider'=>$products,
								'columns' => array(

									array(
							            'name'=>'filename',
										'type' => 'raw',
										'value'=>'CHtml::link(CHtml::image($data->filename,$data->description,array("class"=>"image img-square img-120")),Yii::app()->createUrl(\'products/update\',array(\'id\'=>crypt::Encrypt($data->id_product))))',
										'htmlOptions'=>array('style'=>'width: 150px;'),
							        ),
									array(
							            'name'=>'title',
										'value'=>'$data->title',
											'htmlOptions'=>array('style'=>'vertical-align: middle'),
							        ),
									array(
							            'name'=>'description',
										'value'=>'$data->description',
										'htmlOptions'=>array('style'=>'vertical-align: middle'),
							        ),
									array(
							            'name'=>'price',
										'type' => 'raw',
							            'value'=>'$data->price',
													'htmlOptions'=>array('style'=>'vertical-align: middle'),
							        ),
									array(
										'class'=>'CButtonColumn',
										'template'=>'{remove}',
										'buttons'=>array(
            								'remove'=>array(
												'url'=>'Yii::app()->createUrl("products/delete", array("id"=>crypt::Encrypt($data->id_product)))',
												//'url' =>'#',
												'label'=>'',
												'imageUrl'=>Yii::app()->request->baseUrl.'/css/images/cancel.png',
												'options'=>array(
													'class'=>'delete',
													'style'=>'vertical-align: middle',
													// 'onclick'=>'askDelete();'
												),
											),
            							)
									)
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

<!-- CANCELLAZIONE POS -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="cancelModalLabel">Conferma Cancellazione</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p>
					Sei sicuro di voler cancellare questo <?php echo $viewName;?>?
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<a href="<?php echo $deleteURL;?>">
					<button type="button" class="btn btn-danger">Conferma</button>
				</a>
			</div>
		</div>
	</div>
</div>
