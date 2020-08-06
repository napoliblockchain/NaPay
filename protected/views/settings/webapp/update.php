<?php
$viewName = 'Pos';
if (empty($model->id_gateway))
	$model->id_gateway = 1;

// $gateways=Gateways::model()->findByPk($model->id_gateway);
//
// $urlPairing = Yii::app()->createUrl('settings/'.$gateways->action_controller.'Pairing'); // controller che fa il pairing
// $urlRevoke = Yii::app()->createUrl('settings/'.$gateways->action_controller.'Revoke'); // controller che revoca il pairing
// $urlReturn = Yii::app()->request->url;
// $id_pos = crypt::Encrypt(0);

$deleteURL = Yii::app()->createUrl('settings/posDelete').'&id='.crypt::Encrypt(0);
$revokeURL = Yii::app()->createUrl('settings/posRevoke').'&id='.crypt::Encrypt(0);


$tabList['GDPR']   = array('id'=>'gdpr','content'=>$this->renderPartial('webapp/_gdpr',array('model'=>$model),TRUE));
$tabList['Quote']   = array('id'=>'quote','content'=>$this->renderPartial('webapp/_quote',array('model'=>$model),TRUE));
$tabList['Server host']   = array('id'=>'serverhost','content'=>$this->renderPartial('webapp/_serverhost',array('model'=>$model),TRUE));
$tabList['POA & Token']   = array('id'=>'poa','content'=>$this->renderPartial('webapp/_poa',array('model'=>$model),TRUE));
// $tabList['BTCPay Server']   = array('id'=>'btcpayserver','content'=>$this->renderPartial('webapp/_btcpayserver',array('model'=>$model),TRUE));
// $tabList['SIN & Pairings']   = array('id'=>'sin','content'=>$this->renderPartial('webapp/_sin',array('model'=>$model),TRUE));
$tabList['Exchange']   = array('id'=>'exchange','content'=>$this->renderPartial('webapp/_exchange',array('model'=>$model, ),TRUE));
$tabList['Socials']   = array('id'=>'socials','content'=>$this->renderPartial('webapp/_socials',array('model'=>$model),TRUE));
$tabList['Vapid Push']   = array('id'=>'vapid','content'=>$this->renderPartial('webapp/_vapid',array('model'=>$model, ),TRUE));
$tabList['Paypal']   = array('id'=>'paypal','content'=>$this->renderPartial('webapp/_paypal',array('model'=>$model, ),TRUE));
$tabList['reCaptcha2']   = array('id'=>'recaptcha','content'=>$this->renderPartial('webapp/_recaptcha',array('model'=>$model, ),TRUE));
$tabList['Negozio']   = array('id'=>'store','content'=>$this->renderPartial('webapp/_store',array('model'=>$model),TRUE));
$tabList['POS']   = array('id'=>'pos','content'=>$this->renderPartial('webapp/_pos',array('model'=>$model),TRUE));
// $tabList['Socials']   = array('id'=>'socials','content'=>$this->renderPartial('webapp/_socials',array('model'=>$model),TRUE));
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<h2 class='title-1 m-b-25'><small>Impostazioni applicazione</small></h2>
					</div>
					<div class="card-body card-block">

						<?php $this->widget('zii.widgets.jui.CJuiTabs',array(

							'tabs' => $tabList,
							'options'=>array(
								'collapsible'=>true,
							),
							'id'=>'MyTab-Menu',
						));
						?>
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
