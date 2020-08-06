<?php
/* @var $this StoresController */
/* @var $model Stores */
$viewName = 'Impostazioni';
(Yii::app()->user->objUser['privilegi'] == 20) ? $visible_association = true : $visible_association = false;


//VISIBILI ALL'USER MERCHANT
$attributes[] = array(
					'label'=>'Gateway',
					'value'=>isset(Gateways::model()->findByPk($model->id_gateway)->denomination) ? Gateways::model()->findByPk($model->id_gateway)->denomination : "",
				);
$attributes[] = array(
					'label'=>'Exchange',
					'value'=>isset(Exchanges::model()->findByPk($model->id_exchange)->denomination) ? Exchanges::model()->findByPk($model->id_exchange)->denomination : "",
				);


if (Yii::app()->user->objUser['privilegi'] == 10){
	$attributes[] =	'exchange_key';
	$attributes[] =	'withdrawal_exchange_key';
	$attributes[] =	'only_for_bitstamp_liquidation_deposit_address';
	// $attributes[] = array(
	// 				'label'=>'Indirizzo token principale',
	// 				'value'=>Wallets::model()->findByPk($model->id_wallet)->wallet_address
	// 			);

	$attributes[] =	'iban';

}



//VISIBILI ALL'ASSOCIAZIONE
$attributes[] = array(
					'label'=>'Percentuale',
					'value'=>$model->association_percent.' %',
					'visible'=>$visible_association
				);

$attributes[] = array(
					'label'=>'Indirizzo BTC di '.Yii::app()->params['shortName'],
					'value'=>$model->association_receiving_address,
					'visible'=>$visible_association
				);






//VISIBILI ALL'ADMIN
if (Yii::app()->user->objUser['privilegi'] == 20){
		$attributes[] =	'BTCPayServerAddress';
		$attributes[] = array(
							'label'=>'Indirizzo rete POA (token)',
							'value'=>$model->poa_url
						);
		$attributes[] = array(
							'label'=>'Porta rete POA (token)',
							'value'=>$model->poa_port
						);

		$attributes[] = array(
							'label'=>'Quota iscrizione Persona Giuridica',
							'value'=>$model->quota_iscrizione_socioGiuridico . ' (€)',
						);
		// $attributes[] = array(
		// 					'label'=>'Sin Associazione',
		// 					'value'=>$model->pos_sin,
		// 				);

}



#echo '<pre>'.print_r($attributes,true).'</pre>';
#exit;
?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>
	<div class="row">
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fa fa-gear"></i>
					<span class="card-title"><?php echo $viewName; ?></span>
				</div>
				<div class="card-body">
					<div class="table-responsive table--no-card ">
						<?php $this->widget('zii.widgets.CDetailView', array(
							// 'htmlOptions' => array('class' => 'table table-borderless table-striped '),
							'data'=>$model,
							'attributes'=>$attributes,
						));
						?>
					</div>
				</div>
				<div class="card-footer">
					<div class="row">
						<div class="col-lg-12">
							<div class="overview-wrap">
								<h2 class="title-1">
									<?php
										if (Yii::app()->user->objUser['privilegi'] == 20)
											$modifyURL = Yii::app()->createUrl('settings/update').'&id='.crypt::Encrypt(0);
										else
											$modifyURL = Yii::app()->createUrl('settings/update').'&id='.crypt::Encrypt($model->id_user);
									?>
									<?php
									$merchants = Merchants::model()->findByAttributes(array('id_user'=>$model->id_user));
									//$configURL = Yii::app()->createUrl('merchants/config').'&id='.crypt::Encrypt($merchants->id_merchant);
									//$configTokenURL = Yii::app()->createUrl('settings/token').'&id='.crypt::Encrypt($model->id_user);

									//if ($model->id_gateway == 1){
									?>
									<!-- <a href="<?php //echo $configURL;?>">
										<button type="button" class="btn btn-primary">Wallet Bitcoin</button>
									</a> -->
									<?php //} ?>
									<!-- <a href="<?php //echo $configTokenURL;?>">
										<button type="button" class="btn btn-secondary">Wallet Token</button>
									</a> -->

									<a href="<?php echo $modifyURL;?>">
										<button type="button" class="btn btn-warning">Modifica</button>
									</a>

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
