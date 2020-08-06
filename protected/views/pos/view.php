<?php
/* @var $this PosController */
/* @var $model Pos */
$viewName = 'Pos';


//RICERCA GATEWAY PER INVIARE COMANDI PERSONALIZZATI
$merchants=Merchants::model()->findByAttributes(array('id_merchant'=>$model->id_merchant));
//$settings=Settings::model()->findByAttributes(array('id_user'=>$merchants->id_user));
$settings=Settings::loadUser($merchants->id_user);
if (empty($settings->id_gateway))
	$settings->id_gateway = 1;
$gateways=Gateways::model()->findByPk($settings->id_gateway);

$urlACTIVATEPOS = Yii::app()->createUrl('pos/activate'); // controller che crea il token per il pairing su BTCPayserver
// $urlSaveMpk = Yii::app()->createUrl('pos/savempk'); // controller che crea il token per il pairing su BTCPayserver

$urlPairing = Yii::app()->createUrl('pos/'.$gateways->action_controller.'Pairing'); // controller che fa il pairing
$urlRevoke = Yii::app()->createUrl('pos/'.$gateways->action_controller.'Revoke'); // controller che revoca il pairing
$urlReturn = Yii::app()->request->url;
$urlMailSin = Yii::app()->createUrl('pos/sendmail');

$id_pos = crypt::Encrypt($model->id_pos);
#echo $urlPairing;
#exit;

$pairing = <<<JS
	var ajax_loader_url = 'css/images/loading.gif';

	$("#pairing-button").click(function() {
		btcpay.pairing();
	});

	// $("#pairingCode-button").click(function() {
	// 	pairingCode = $('#pairingCode').val();
	// 	console.log(pairingCode);
	// 	btcpay.pairing(pairingCode);
	// });

	var btcpay = {
		pairing: function(){
			$.ajax({
				url:'{$urlPairing}',
				type: "POST",
				data:{
					'pairingCode'	: '{$model->pairingCode}',
				    'label'			: '{$model->denomination}',
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

	$("#mailButton").click(function() {
		$.ajax({
			url:'{$urlMailSin}',
			type: "POST",
			data:{
				'id_pos'	: '{$id_pos}',
			},
			dataType: "json",
			beforeSend: function(){
				$('#mailButton').hide();
				$('#mail-response').show();
				$('#mail-response').after('<div class="bitpay-pairing__loading"><img width=15 src="'+ajax_loader_url+'"></div>');
			},
			success:function(data){
				$('#mailButton').show();
				$('.bitpay-pairing__loading').remove();
				$('#mail-response').html(data.message);

			},
			error: function(j){
				$('#mailButton').show();
				$('.bitpay-pairing__loading').remove();
				message = "<b>ERROR!</b> Something was wrong";
				$('#mail-response').html(message);
			}
		});
	});
JS;
Yii::app()->clientScript->registerScript('pairing', $pairing);

$Pairings = Pairings::model()->findByAttributes(array('id_pos'=>$model->id_pos));
//$label = '';
$sin = '';
if (isset($Pairings->sin)){
	//$label = $Pairings->label;
	$sin = $Pairings->sin;
}
(Yii::app()->user->objUser['privilegi'] == 20 ? $message = 'Vuoi inviare il SIN via mail al commerciante?' : $message = 'Vuoi ricevere il SIN via mail?');

$http_host = $_SERVER['HTTP_HOST'];
$posLink = 'https://pos.' . Utils::get_domain($http_host) . '/index.php?r=site/login&sin='.$sin
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
									'denomination',
									array(
							            'label'=>'Pairing Code',
										'type'=>'raw',
							            'value'=>$model->pairingCode,
										'visible'=>($model->pairingCode != '' ? true : false),
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
										<h2 class="text-primary">
											Un ultimo passo...<br>
										</h2>
										<p class="text-dark">Il POS è stato creato. Per renderlo operativo clicca sul pulsante <b class="text-primary">"ATTIVA"</b>.</p>
										<p class="text-warning"><b>
											Ricordati che hai un tempo massimo di 15 minuti, oltre il quale non sarà più possibile effettuare l'attivazione.
										</b>
										</p>
										<p class="text-dark">Al termine dell'operazione potrai visualizzare il <b>SIN</b> (Server-Initiated Pairing) che è il codice che dovrai utilizzare per accedere al POS.<br>
											Riceverai tramite mail un riepilogo di tutte queste informazioni.</p>
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
						$modifyURL = Yii::app()->createUrl('pos/update').'&id='.crypt::Encrypt($model->id_pos);
						$deleteURL = Yii::app()->createUrl('pos/delete').'&id='.crypt::Encrypt($model->id_pos);
						$revokeURL = Yii::app()->createUrl('pos/revoke').'&id='.crypt::Encrypt($model->id_pos);

						if (Yii::app()->user->objUser['privilegi'] > 5){
							if ($sin != ''){
						?>
							<div class="row">
								<div class="col-md-6">

										<span>
										<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#revokeModal">Revoca</button>
										</span>
										<span style="padding-left:20px;">
										<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mailModal">Invia Mail</button>
										</span>
										<span style="padding-left:20px;">

										<button type="button" class="btn btn-dark" data-toggle="modal" data-target="#posModal">Apri POS</button>
										</span>

								</div>
							</div>
							<div class="row">
								<div class="col">
									<p><br>
										<span id="mail-response" class="alert alert-primary" style="display:none;"></span>
									</p>
								</div>
							</div>

						<?php }else{ ?>
							<div class="row nascondiDopoPairing">
								<div class="col-md-6">
									<div class="overview-wrap">
										<h2 class="title-1">
											<a href="<?php echo $modifyURL;?>">
												<button type="button" class="btn btn-warning">Modifica</button>
											</a>
											<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelModal">Elimina</button>
										</h2>
									</div>
								</div>
							</div>
						<?php }
						}
						?>
					</div>
				</div>
			</div>
		</div>

		<?php echo Logo::footer(); ?>
	</div>
</div>
<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>

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
<!-- REVOCA LINK POS -->
<div class="modal fade" id="revokeModal" tabindex="-1" role="dialog" aria-labelledby="revokeModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="revokeModalLabel">Conferma Revoca Connessione</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p>
					Sei sicuro di voler revocare la connessione di questo <?php echo $viewName;?>?
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<a href="#">
					<button type="submit" class="btn btn-danger" id="revokeButton">Conferma</button>
				</a>
			</div>
		</div>
	</div>
</div>
<!-- INVIO MAIL DEL SIN DEL POS -->
<div class="modal fade" id="mailModal" tabindex="-1" role="dialog" aria-labelledby="mailModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="mailModalLabel">Invio Mail</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p>
					<?php echo $message; ?>
				</p>
			</div>
			<div class="modal-footer">
				<div class="form-group">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				</div>
				<div class="form-group">
					<button type="button" class="btn btn-primary" id="mailButton" data-dismiss="modal">Conferma</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- LINK AL POS -->
<div class="modal fade" id="posModal" tabindex="-1" role="dialog" aria-labelledby="posModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="posModalLabel">Apri POS</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p>
					<img src="css/images/pos.png" width="150" height="150" />
					Questo link aprirà una nuova finestra per collegarsi al POS. Vuoi continuare?
				</p>
			</div>
			<div class="modal-footer">
				<div class="form-group">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				</div>
				<div class="form-group">
					<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.open('<?php echo $posLink; ?>','POS')">Conferma</button>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>
