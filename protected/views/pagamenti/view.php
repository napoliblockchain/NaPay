<?php
$viewName = 'Pagamento';

(Yii::app()->user->objUser['privilegi'] == 20) ? $visible = true : $visible = false;
?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>
	<div class="row">
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fas fa-credit-card"></i>
					<span class="card-title">Dettagli <?php echo $viewName;?></span>
				</div>
				<div class="card-body">
					<div class="table-responsive table--no-card ">
						<?php $this->widget('zii.widgets.CDetailView', array(
							//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
							'data'=>$model,
							'attributes'=>array(
								'id_invoice_bps',
								array(
									'label'=>'Ricevuta',
									'value'=>$model->progressivo.'/'.$model->anno,
								),
								array(
									'label'=>'Nome',
									'value'=>Users::model()->findByPk($model->id_user)->name,
									'visible'=>$visible,
								),
								array(
									'label'=>'Cognome',
									'value'=>Users::model()->findByPk($model->id_user)->surname,
									'visible'=>$visible,
								),
								array(
									'label'=>'Tipo di iscrizione',
									'value'=>Quote::model()->findByPk($model->id_quota)->description,
								),
								array(
									'type'=>'raw',
									'name'=>'status',
									//'value'=>WebApp::walletStatus($model->status),
									'value' => ( $model->status != "complete" || $model->status <> "paid" ) ?
										(
											CHtml::ajaxLink(
											    WebApp::walletStatus($model->status),          // the link body (it will NOT be HTML-encoded.)
											    array('backend/checkSinglePayment'."&id=".CHtml::encode(crypt::Encrypt($model->id_pagamento))), // the URL for the AJAX request. If empty, it is assumed to be the current URL.
											    array(
											        'update'=>'.btn-outline-dark',
											        'beforeSend' => 'function() {
											           		$(".btn").text("Checking...");
																		$(".btn").addClass("alert-warning text-light");
											        	}',

											        'complete' => 'function(data) {
														//$(".btn").removeClass("alert-warning");
														console.log(data);
														var obj = JSON.parse(data.responseText)
														$(".btn").text(obj.message);
														var time = 1;

														if (obj.success == 1){
															function waitUntil(time){
																time -= 1;
																$(".btn").text(obj.message+ " (- "+time+")");

																if (time >0)
																	setTimeout(function(){ waitUntil(time) }, 1000);
																else
																	location.reload();
															}
															setTimeout(function(){ waitUntil(time) }, 1000);
														}
														else{
															$(".btn").text("Transazione non trovata.");
															// $("#btn-delete-transaction").text("ELIMINA");
															// $(".delete-transaction").show();
														}
											        }',
											    )
											)
										) : WebApp::walletStatus($model->status),
								),
								'importo',
								//'id_tipo_pagamento',
								array(
									'label'=>'Tipo di pagamento',
									'value'=>TipoPagamenti::model()->findByPk($model->id_tipo_pagamento)->description,
								),
								array(
									'type'=>'raw',
									'label'=>'Transazione Paypal',
									'value'=>$model->paypal_txn_id,
									'visible'=>($model->id_tipo_pagamento == 2 ? true:false),
								),
								array(
									'label'=>'Data Registrazione',
									'value'=>WebApp::data_it($model->data_registrazione),
								),
								array(
									'label'=>'Data Inizio',
									'value'=>WebApp::data_it($model->data_inizio),
								),
								array(
									'label'=>'Data Scadenza',
									'value'=>WebApp::data_it($model->data_scadenza),
								),
							),
						));
						?>
					</div>
				</div>
				<div class="card-footer">
					<?php
						$modifyURL = Yii::app()->createUrl('pagamenti/update').'&id='.crypt::Encrypt($model->id_pagamento);
						$printURL = Yii::app()->createUrl('pagamenti/print').'&id='.crypt::Encrypt($model->id_pagamento);
					?>

					<div class="row">
						<div class="col-md-6">
							<div class="overview-wrap">
								<h2 class="title-1">
									<?php if ($model->progressivo <> 0){ ?>
										<a href="<?php echo $printURL;?>" target="_blank">
											<button type="button" class="btn btn-secondary">Stampa</button>
										</a>

									<?php } ?>
									<?php if (Yii::app()->user->objUser['privilegi'] == 20){
										$users = Users::model()->findByPk(Yii::app()->user->objUser['id_user']);
										if ($users->id_carica != 4){ ?>
											<a href="<?php echo $modifyURL;?>">
												<button type="button" class="btn btn-warning">Modifica</button>
											</a>
										<?php } ?>
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
