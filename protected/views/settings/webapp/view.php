<?php
/* @var $this StoresController */
/* @var $model Stores */
$viewName = 'Impostazioni';


//VISIBILI ALL'USER MERCHANT
$attributes[] = array(
					'label'=>'Exchange',
					'value'=>Exchanges::model()->findByPk(array('id_exchange'=>$model->id_exchange))->denomination
				);
$attributes[] =	'exchange_key';

if (Yii::app()->user->objUser['privilegi'] == 10){
$attributes[] = array(
					'label'=>'Indirizzo token principale',
					'value'=>Wallets::model()->findByPk($model->id_wallet)->wallet_address
				);
}



//VISIBILI ALL'ASSOCIAZIONE
// $attributes[] = array(
// 					'label'=>'Percentuale',
// 					'value'=>$model->association_percent.' %',
// 				);

// $attributes[] = array(
// 					'label'=>'Indirizzo BTC di '.Yii::app()->params['shortName'],
// 					'value'=>$model->association_receiving_address,
// 				);



// echo '<pre>'.print_r($model,true).'</pre>';
// exit;


//VISIBILI ALL'ADMIN
if (Yii::app()->user->objUser['privilegi'] == 20){
		// $attributes[] =	'BTCPayServerAddress';
		// $attributes[] = array(
		// 					'label'=>'Indirizzo rete POA (token)',
		// 					'value'=>$model->poa_url
		// 				);
		// $attributes[] = array(
		// 					'label'=>'Chain Id POA (token)',
		// 					'value'=>(isset($model->poa_chainId) ? $model->poa_chainId : '')
		// 				);
		$attributes[] = array(
							'label'=>'Smart Contract POA (token)',
							'value'=>(isset($model->poa_contractAddress) ? $model->poa_contractAddress : '')
						);
		$attributes[] = array(
							'label'=>'Scadenza Invoice',
							'value'=>$model->poa_expiration . ' (min)',
						);

		$attributes[] = array(
							'label'=>'Quota iscrizione socio ordinario',
							'value'=>$model->quota_iscrizione_socio . ' (€)',
						);
		$attributes[] = array(
							'label'=>'Quota iscrizione Persona Giuridica',
							'value'=>$model->quota_iscrizione_socioGiuridico . ' (€)',
						);
		$attributes[] = array(
							'label'=>'Sin Associazione',
							'value'=>isset($model->pos_sin) ? $model->pos_sin : '',
						);

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
						<div class="col-md-2">
							<div class="overview-wrap">
								<h2 class="title-1">
									<?php
										if (Yii::app()->user->objUser['privilegi'] == 20)
											$modifyURL = Yii::app()->createUrl('settings/update').'&id='.crypt::Encrypt(0);
										else
											$modifyURL = Yii::app()->createUrl('settings/update').'&id='.crypt::Encrypt($model->id_user);

										$users = Users::model()->findByPk(Yii::app()->user->objUser['id_user']);
										if ($users->id_carica != 4){
									?>
											<a href="<?php echo $modifyURL;?>">
												<button type="button" class="btn btn-warning">Modifica</button>
											</a>

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
