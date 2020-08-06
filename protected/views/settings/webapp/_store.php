<?php
$viewName = 'Negozio';
$visible = false;


 // echo '<pre>'.print_r($model->attributes,true).'</pre>';
 // exit;
if (isset($model->id_store)) {
	$storeSettings = Settings::loadStore($model->id_store);

	$modifyURL = Yii::app()->createUrl('settings/storeUpdate').'&id='.crypt::Encrypt($model->id_store);
	$deleteURL = Yii::app()->createUrl('settings/storeDelete',['id'=>crypt::Encrypt($model->id_store)]);
	// controller di modifica impostazioni store
	$general_settings = Yii::app()->createUrl('settings/storeGeneral',['id'=>crypt::Encrypt($model->id_store)]);
	$exchange = Yii::app()->createUrl('settings/storeExchange',['id'=>crypt::Encrypt($model->id_store)]);
	$checkout = Yii::app()->createUrl('settings/storeCheckout',['id'=>crypt::Encrypt($model->id_store)]);
	$checkoutLogo = Yii::app()->createUrl('settings/storeCheckoutLogo',['id'=>crypt::Encrypt($model->id_store)]);
	$checkoutCss = Yii::app()->createUrl('settings/storeCheckoutCss',['id'=>crypt::Encrypt($model->id_store)]);
	$buttonLoadLogo = '<a class="float-right" href="'.$checkoutLogo.'"><button class="btn btn-info" style="padding: 0px 15px 0px 15px;">Carica</button></a>';
	$buttonLoadCss = '<a class="float-right" href="'.$checkoutCss.'"><button class="btn btn-info" style="padding: 0px 15px 0px 15px;">Carica</button></a>';
	?>
	<div class='section__content section__content--p30'>
		<div class='container-fluid'>
			<div class="row">
				<div class="col-lg-12">
					<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
						<div class="card-header ">
							<i class="fas fa-shopping-cart"></i>
							<span class="card-title">Dettagli <?php echo $viewName;?></span>
						</div>
						<div class="card-body">
							<div class="table-responsive table--no-card ">
								<?php $this->widget('zii.widgets.CDetailView', array(
									//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
									'data'=>$model,
									'attributes'=>array(
										'store_denomination',
										array(
											'label'=>'ID Negozio',
											'value'=>$storeSettings->bps_storeid
										)

										//'nation',

									),
								));
								?>
							</div>
						</div>
						<div class="card-footer">
							<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
											<a href="<?php echo $modifyURL;?>">
												<button type="button" class="btn btn-warning btn-sm">Modifica</button>
											</a>
											<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#mediumModal">Elimina</button>
							<?php } ?>
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
										[	'label'=>'Website Negozio',
											'value'=>$storeSettings->store_website
										],
										[	'label'=>'Network Fee',
											'value'=>$storeSettings->network_fee_mode
										],
										[	'label'=>'Scadenza Invoice',
											'value'=>$storeSettings->invoice_expiration
										],
										[	'label'=>'Scadenza monitoraggio invoice',
											'value'=>$storeSettings->monitoring_expiration
										],
										[	'label'=>'Tolleranza sul pagamento',
											'value'=>$storeSettings->payment_tolerance .'%'
										],
										[	'label'=>'Conferme',
											'value'=>$storeSettings->speed_policy
										],
									),
								));
								?>
							</div>
						</div>
						<div class="card-footer">
							<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
									<a href="<?php echo $general_settings;?>">
										<button class="btn btn-warning btn-sm">Modifica</button>
									</a>
							<?php } ?>

						</div>
					</div>
				</div>
			</div>

			<!-- Exchange rate -->
			<div class="row">
				<div class="col-lg-12">
					<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
						<div class="card-header">
							<span class="card-title">Exchange Rate</span>
						</div>
						<div class="card-body">
							<div class="table-responsive table--no-card">
								<?php
								$this->widget('zii.widgets.CDetailView', array(
									//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
									'data'=>$model,
									'attributes'=>array(
										[	'label'=>'Exchange preferito',
											'value'=>$storeSettings->preferred_exchange
										],
										[	'label'=>'Spread',
											'value'=>$storeSettings->spread
										],
										[	'label'=>'Coppia valute di default',
											'value'=>$storeSettings->default_currency_pairs
										],
									),
								));
								?>
							</div>
						</div>
						<div class="card-footer">
							<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
								<a href="<?php echo $exchange;?>">
									<button class="btn btn-warning btn-sm">Modifica</button>
								</a>
							<?php } ?>

						</div>
					</div>
				</div>
			</div>

			<!-- master public key -->
			<?php

			$enabledAssets = CJSON::decode($model->blockchainAsset);
			foreach ($enabledAssets as $key => $asset){

				$mpk=Mpk::model()->findByAttributes(['id_store'=>$model->id_store,'asset'=>$asset]);
				$savempk = Yii::app()->createUrl('settings/storeSavempk',['id'=>crypt::Encrypt($model->id_store),'asset'=>$asset]);
				?>
				<div class="row">
					<div class="col-lg-12">
						<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
							<div class="card-header">
								<span class="card-title">Master Public Key <b><?php echo $asset; ?></b></span>
							</div>
							<div class="card-body">
								<div class="table-responsive table--no-card ">
									<?php
									$this->widget('zii.widgets.CDetailView', array(
										//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
										'data'=>$model,
										'attributes'=>array(
											[	'label'=>'Tipo Indirizzi',
												'value'=>empty($mpk->AddressType) ? '' : $mpk->AddressType
											],
											[	'label'=>'Master Public Key',
												'type'=>'raw',
												'value'=>wordwrap(empty($mpk->DerivationScheme) ? '' : $mpk->DerivationScheme, 80, "<br>", true)
											],
											[	'label'=>'Indirizzi',
												'type'=>'raw',
												'value'=>WebApp::mpkAddressesView(empty($mpk->Addresses) ? '' : $mpk->Addresses)
											],
										),
									));
									?>
								</div>
							</div>
							<div class="card-footer">
								<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
									<a href="<?php echo $savempk;?>">
										<button class="btn btn-warning btn-sm">Modifica</button>
									</a>
								<?php } ?>

							</div>
						</div>
					</div>
				</div>
			<?php } ?>

			<!-- tema invoice -->
			<div class="row">
				<div class="col-lg-12">
					<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
						<div class="card-header">
							<span class="card-title">Tema Invoice</span>
						</div>
						<div class="card-body">
							<div class="table-responsive table--no-card">
								<?php
								$this->widget('zii.widgets.CDetailView', array(
									//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
									'data'=>$model,
									'attributes'=>array(
										[	'label'=>'Logo personalizzato',
											'type'=>'raw',
											'value'=>$storeSettings->CustomLogo . $buttonLoadLogo
										],
										[	'label'=>'CSS Personalizzato',
											'type'=>'raw',
											'value'=>$storeSettings->CustomCSS . $buttonLoadCss
										],
										[	'label'=>'Titolo della pagina html',
											'value'=>$storeSettings->HtmlTitle
										],
										[	'label'=>'Valuta di default',
											'value'=>$storeSettings->DefaultPaymentMethod
										],
										[	'label'=>'Lingua predefinita',
											'value'=>$storeSettings->DefaultLang
										],
										[	'label'=>'richiesta e-Mail dell\'acquirente',
											'value'=>$storeSettings->RequiresRefundEmail
										],
										[	'label'=>'Non utilizzare LN per importi < di ...',
											'value'=>$storeSettings->OnChainMinValue
										],
										[	'label'=>'Non utilizzare LN per importi > di ...',
											'value'=>$storeSettings->LightningMaxValue
										],
										[	'label'=>'Mostra pagamenti LN in satoshi',
											'value'=>$storeSettings->LightningAmountInSatoshi
										],
										[	'label'=>'Ritorna automaticamente a redirectURL',
											'value'=>$storeSettings->RedirectAutomatically
										],
										[	'label'=>'Mostra le fee raccomandate',
											'value'=>$storeSettings->ShowRecommendedFee
										],
										[	'label'=>'N. di blocchi da considerare per le fee raccomandate',
											'value'=>$storeSettings->RecommendedFeeBlockTarget
										],

									),
								));
								?>
							</div>
						</div>
						<div class="card-footer">
							<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
								<a href="<?php echo $checkout;?>">
									<button class="btn btn-warning btn-sm">Modifica</button>
								</a>
							<?php } ?>

						</div>
					</div>
				</div>
			</div>


		</div>
	</div>
	<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
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
					<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Annulla</button>
					<a href="<?php echo $deleteURL;?>">
						<button type="button" class="btn btn-primary btn-sm">Conferma</button>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php }
}else{
	 $this->renderPartial('webapp/stores/create', array('model'=>$model));
} ?>
