<?php
//RICERCA GATEWAY PER INVIARE COMANDI PERSONALIZZATI
// if (empty($settings->id_gateway))
// 	$settings->id_gateway = 1;
//
$gateways=Gateways::model()->findByPk($model->id_gateway);
//
$urlPairing = Yii::app()->createUrl('settings/'.$gateways->action_controller.'Pairing'); // controller che fa il pairing
$urlRevoke = Yii::app()->createUrl('settings/'.$gateways->action_controller.'Revoke'); // controller che revoca il pairing
$urlReturn = Yii::app()->request->url;
//
$id_pos = crypt::Encrypt(0);
#echo $urlPairing;
#exit;

$pairing = <<<JS
	var ajax_loader_url = 'css/images/loading.gif';

	$("#pairing-button").click(function() {
		btcpay.pairing();
	});


	var btcpay = {
		pairing: function(){
			$.ajax({
				url:'{$urlPairing}',
				type: "POST",
				data:{
					'pairingCode'	: '{$model->pos_pairingCode}',
				    'label'			: '{$model->pos_denomination}',
					'id_pos'		: '{$id_pos}',
				},
				dataType: "json",
				beforeSend: function(){
					$('.token-message2').addClass('alert-info').show();
					$('.token-message2').html('<div class="pairing__loading"><img width=20 src="'+ajax_loader_url+'"></div>');
				},
				success:function(data){
					$('.pairing__loading').remove();

					if (data.error){
						$('.pairing-button').show().fadeIn(500);
						$('.token-message2').removeClass("alert-info");
						$('.token-message2').addClass("alert-danger");
						$('.token-message2').html(data.error);
						return false;
					}else{
				   		$('.bitpay-token__token-sin').text(data.sin);
						$('.bitpay-token__token-token').text(data.token);
						$('.nascondiDopoPairing').hide();
						$('.token-message2').removeClass("alert-info");
						$('.token-message2').addClass("alert-success");
						$('.token-message2').html('<b>Perfetto! Hai abilitato correttamente il POS.</b>');
					}
				},
				error: function(j){
					message = "<b>ERROR!</b></font> Something was wrong!";
					$('.token-message2').removeClass('alert-info');
					$('.token-message2').addClass('alert-danger');
					$('.token-message2').html(message);
				}
			});
		}
	}




	$("#revokeButton").click(function() {
		$.ajax({
			url:'{$urlRevoke}',
			type: "POST",
			data:{
				'id_pos'	: '{$id_pos}',
			},
			dataType: "json",
			success:function(data){
				window.location.href = '{$urlReturn}';
			},
			error: function(j){
				message = "<p style='color:#555;'><font style='color:#f00;'><b>ERROR!</b></font> Something was wrong!</p>";
				$('#bitpay-pairing__message').text(message);
			}
		});
	});


JS;
Yii::app()->clientScript->registerScript('pairing', $pairing);

$Pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));
//$label = '';
$sin = '';
if (isset($Pairings->sin)){
	//$label = $Pairings->label;
	$sin = $Pairings->sin;
}

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad  bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-desktop"></i>
						<span class="card-title">Dettagli Pos</span>
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
									array(
							            'label'=>'Pairing Code',
										'type'=>'raw',
							            'value'=>$model->pos_pairingCode,
										'visible'=>($model->pos_pairingCode != '' ? true : false),
							        ),
									array(
							            'label'=>'Sin',
										'type'=>'raw',
							            'value'=>'<span class="bitpay-token__token-sin">'.$sin.'</span>'
							        ),
								),
							));
							if ($sin == '' ){
								?>
								<div class="typo-articles nascondiDopoPairing">
									<div class="jumbotron">
									<p class="text-warning"><b>
											Ricordati che hai un tempo massimo di 15 minuti, oltre il quale non sarà più possibile effettuare l'attivazione.
										</b>
										</p>
										<p class="text-dark">Al termine dell'operazione potrai visualizzare il <b>SIN</b> (Server-Initiated Pairing).<br>
										L'applicazione utilizzerà questo codice per ricevere le iscrizioni in cryptovaluta.	</p>
										<div class="form-group">
											<button type="button" id='pairing-button' class="btn btn-primary" style="min-width:100px;"><span class="pairing-button">ATTIVA</span></button>
										</div>

									</div>
								</div>
								<div class="token-message2 alert" style="display:none;"></div>
							<?php } ?>

						</div>
					</div>
					<div class="card-footer">
						<?php
						$modifyURL = Yii::app()->createUrl('settings/posUpdate').'&id='.crypt::Encrypt($id_pos);

						if ($sin <> ''){
						?>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#revokeModal">Revoca</button>
									</div>
								</div>
							</div>
						<?php }else{ ?>
							<div class="row nascondiDopoPairing">
								<div class="col-md-6">
									<a href="<?php echo $modifyURL;?>">
										<button type="button" class="btn btn-warning">Modifica</button>
									</a>
									<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelModal">Elimina</button>
								</div>
							</div>
						<?php
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
