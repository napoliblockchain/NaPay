<?php
/* @var $this MerchantsController */
/* @var $model Merchants */
$active = ['No','Si'];
$viewName = 'Commerciante';

?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>
	<div class="row">
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fas fa-industry"></i>
					<span class="card-title">Dettagli <?php echo $viewName;?></span>
				</div>
				<div class="card-body">
					<div class="table-responsive table--no-card ">
						<?php $this->widget('zii.widgets.CDetailView', array(
							//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
							'data'=>$model,
							'attributes'=>array(
								//'id_merchant',
								//'alias',

								'denomination',
								'address',
								//'cap',
								array(
						            'label'=>'Città',
						            'value'=>$model->cap.' - '.
											ComuniItaliani::model()->findByPk($model->city)->citta.
												' ('. ComuniItaliani::model()->findByPk($model->city)->sigla.') - '.
											$model->county
						        ),
								//'county',

								array(
						            'label'=>'Nome e Cognome',
						            'value'=>(isset(Users::model()->findByAttributes(array('id_user'=>$model->id_user))->name) ? Users::model()->findByAttributes(array('id_user'=>$model->id_user))->name : '')  . ' ' .
											 (isset(Users::model()->findByAttributes(array('id_user'=>$model->id_user))->surname) ? Users::model()->findByAttributes(array('id_user'=>$model->id_user))->surname : '')
						        ),
								// array(
						        //     'label'=>'Cognome',
						        //     'value'=>
						        // ),
								array(
						            'label'=>'Email',
						            'value'=>(isset(Users::model()->findByAttributes(array('id_user'=>$model->id_user))->email) ? Users::model()->findByAttributes(array('id_user'=>$model->id_user))->email : Yii::t('lang','Not found'))
						        ),

								array(
									'type'=> 'raw',
									'name'=>'iscrizione',
									'value'=>WebApp::StatoPagamenti($model->id_user),
								),
								array(
						            'label'=>'Blockchain URL',
						            'value'=>$userSettings->blockchainAddress
						        ),
								[	'label'=>'Asset abilitati',
									'type'=>'raw',
									'value'=>WebApp::coinsEnabledViewMerchants($userSettings->blockchainAsset)
								],
								// array(
						        //     'label'=>'Gateway',
						        //     'value'=>Gateways::model()->findByAttributes(array('id_gateway'=>Settings::loadUser($model->id_user)->id_gateway))->denomination
						        // ),

							),
						));
						?>
					</div>
				</div>
				<div class="card-footer">
					<?php
						$modifyURL = Yii::app()->createUrl('merchants/update').'&id='.crypt::Encrypt($model->id_merchant);
						$deleteURL = Yii::app()->createUrl('merchants/delete').'&id='.crypt::Encrypt($model->id_merchant);
						//$configURL = Yii::app()->createUrl('merchants/config').'&id='.crypt::Encrypt($model->id_merchant);
					?>
						<div class="row">
							<div class="col-md-6">
								<div class="overview-wrap">
									<h2 class="title-1">
										<?php
										// if (Yii::app()->user->objUser['privilegi'] == 20){
										// 	$settings = Settings::loadUser($model->id_user);
										// 	if ($settings->id_gateway == 1){
										// 	?>
										<!--  		<a href="<?php //echo $configURL;?>">
										 			<button type="button" class="btn btn-primary btn-info">Wallet Bitcoin</button>
										 		</a> -->
										<?php //}
										// } ?>
										<?php //if (Yii::app()->user->objUser['privilegi'] == 5){?>
											<!-- <div class="alert alert-danger">
											EFFETTUARE IL LOGOUT E RICOLLEGARSI PER VISUALIZZARE IL NUOVO ACCOUNT DA COMMERCIANTE.
											</div> -->
										<?php //}else{ ?>
										<a href="<?php echo $modifyURL;?>">
											<button type="button" class="btn btn-primary btn-warning">Modifica</button>
										</a>
										<?php //} ?>
										<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
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
				<p>
					Sei sicuro di voler cancellare questo <?php echo $viewName;?>?
				</p>
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
