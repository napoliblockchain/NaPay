<?php
/* @var $this StoresController */
/* @var $model Stores */
$viewName = 'Verbale';
$visible = false;
// if ($model->id_store_bps != '')
// 	$visible = true;
?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>

	<div class="row">
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fas fa-book"></i>
					<span class="card-title">Dettagli <?php echo $viewName;?></span>
				</div>
				<div class="card-body">
					<div class="table-responsive table--no-card">
						<?php $this->widget('bootstrap.widgets.TbDetailView',array(
							//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
							'data'=>$model,
							'attributes'=>array(
								'id_verbali',
								array(
									'label'=>'Data Verbale',
									'value'=>WebApp::data_it($model->data_verbale),
								),
								//'url_verbale',
								'descrizione_verbale',
							),
						)); ?>
					</div>
				</div>
				<div class="card-footer">
					<div class="row">
						<div class="col-md-6">
							<div class="overview-wrap">
								<h2 class="title-1">
									<?php
										$modifyURL = Yii::app()->createUrl('verbali/update').'&id='.crypt::Encrypt($model->id_verbali);
										$deleteURL = Yii::app()->createUrl('verbali/delete').'&id='.crypt::Encrypt($model->id_verbali);
										$printURL = Yii::app()->createUrl('verbali/download').'&id='.crypt::Encrypt($model->id_verbali);
									?>
									<a href="<?php echo $printURL;?>" target="_blank">
										<button type="button" class="btn btn-secondary">Download</button>
									</a>
									<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
										<a href="<?php echo $modifyURL;?>">
											<button type="button" class="btn btn-primary btn-warning">Modifica</button>
										</a>
										<button type="button" class="btn btn-primary btn-danger" data-toggle="modal" data-target="#mediumModal">Elimina</button>
									<?php } ?>
								</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>




	<?php echo Logo::footer(); ?>
</div>
</div>
<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
<div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="mediumModalLabel">Conferma Cancellazione</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Sei sicuro di voler cancellare questo <?php echo $viewName;?>?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<a href="<?php echo $deleteURL;?>">
					<button type="button" class="btn btn-primary btn-danger">Conferma</button>
				</a>
			</div>
		</div>
	</div>
</div>
<?php } ?>
