<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Pagamento';
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'pagaiscrizione-form',
	'enableClientValidation'=>false,
	// 'clientOptions'=>array(
	// 	'validateOnSubmit'=>true,
	// ),
));

//lista tipi pagamento
$criteria=new CDbCriteria();
$criteria->compare('permission',0,false);
$tipopagamenti=TipoPagamenti::model()->findAll($criteria);
$listaTipoPagamenti = CHtml::listData( $tipopagamenti, 'id_tipo_pagamento' , 'description');


//dati per la transazione !
$settingsNapay = Settings::load();
$BtcPayServerIDPOS = crypt::Encrypt(0);
$idUser = Yii::app()->user->objUser['id_user'];

//in base al tipo di personalità (fisica/giuridica) seleziono l'importo da pagare per l'iscrizione
if ($users->corporate == 0)
	$amount = $settingsNapay->quota_iscrizione_socio;
else
	$amount = $settingsNapay->quota_iscrizione_socioGiuridico;

$customer = Yii::app()->user->objUser['email'];
$URLRedirect = 'https://'.$_SERVER['HTTP_HOST'].Yii::app()->createUrl('payfee/review');

// controller che creano la transazione
$urlBitcoin = Yii::app()->createUrl('payfee/bitcoinInvoice');
$urlPaypal = Yii::app()->createUrl('payfee/paypalInvoice');

// Ipn
$URLIpn = 'https://'.$_SERVER['HTTP_HOST'].Yii::app()->createUrl('ipn/iscrizione');
$URLPaypalIpn = 'https://'.$_SERVER['HTTP_HOST'].Yii::app()->createUrl('ipn/paypal');

$myConfirmscript = <<<JS
    var ajax_loader_url = 'css/images/loading.gif';

	$("input[name='yt0']").click(function(){
		event.preventDefault();
		switch ($('#Pagamenti_id_tipo_pagamento').val()){
			case '1': //bitcoin
				$.ajax({
					url:'{$urlBitcoin}',
					type: "POST",
					beforeSend: function() {
						$("input[name='yt0']").hide();
						$("input[name='yt0']").after('<div class="bitpay-pairing__loading"><center><img width=25 src="'+ajax_loader_url+'"></center></div>');
		   			},
					data:{
						'id_pos'		: '{$BtcPayServerIDPOS}',
						'id_user'		: '{$idUser}',
						'amount'		: '{$amount}',
					    'email'			: '{$customer}',
					    'redirectUrl'	: '{$URLRedirect}',
						'ipnUrl'		: '{$URLIpn}',
					},
					dataType: "json",
					success:function(data){
						$('.bitpay-pairing__loading').remove();
						$("input[name='yt0']").show();

						if (data.error){
							$("#__messageBox").show();
							$('#__message').text(data.error);
							return false;
						}else{
							top.location = data.url;
						}
					},
					error: function(j){
		        		var json = jQuery.parseJSON(j.responseText);
						$("input[name='yt0']").show();
						$('.bitpay-pairing__loading').remove();
						$("#__messageBox").show();
						$('#__message').text(json.error);
					}
				});
			break;

			case '2': //PayPal
				$.ajax({
					url:'{$urlPaypal}',
					type: "POST",
					beforeSend: function() {
						$("input[name='yt0']").hide();
						$("input[name='yt0']").after('<div class="bitpay-pairing__loading"><center><img width=25 src="'+ajax_loader_url+'"></center></div>');
					},
					data:{
						'id_user'		: '{$idUser}',
						'amount'		: '{$amount}',
						'email'			: '{$customer}',
						'redirectUrl'	: '{$URLRedirect}',
						'cancelUrl'		: '{$URLRedirect}',
						'ipnUrl'		: '{$URLPaypalIpn}',
					},
					dataType: "json",
					success:function(data){
						if (data.success){
							$("input[name='yt0']").after('<div class="bitpay-pairing__loading alert alert-dark">Stai per essere reindirizzato sul sito di PayPal...</div>');
							top.location = data.url;
						}else{
							$("#__messageBox").show();
							$('#__message').text('Errore: '+data.error);
							$('.bitpay-pairing__loading').remove();
							$("input[name='yt0']").show();
							return false;
						}

					},
					error: function(j){
						//var json = jQuery.parseJSON(j.responseText);
						console.log(j);
					}
				});

			break;
		}
	});
JS;
Yii::app()->clientScript->registerScript('myConfirmscript', $myConfirmscript);
?>

<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fas fa-credit-card"></i>
					<span class="card-title">Paga quota di iscrizione</span>
					<div class="float-right">
						<?php $actionURL = Yii::app()->createUrl('site/logout'); ?>
						<a href="<?php echo $actionURL;?>">
							<button class="btn alert-primary text-light img-cir" style="padding:2.5px 10px 2.5px 10px; height:30px;">
								<i class="fas fa-sign-out-alt"></i> LOGOUT</button>
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="form-group">
						<strong>
							La quota di iscrizione è di: <?php echo $amount; ?>€</center>
						</strong>
					</div>
					<div class="login-form">
						<div class="form-group">
							<?php echo $form->labelEx($model,'Seleziona il tipo di pagamento'); ?>
							<?php echo $form->dropDownList($model,'id_tipo_pagamento',$listaTipoPagamenti,array('class'=>'form-control'));	?>
						</div>
						<?php echo CHtml::button('conferma', array('class' => 'au-btn au-btn--block au-btn--green m-b-20')); ?>
						<div class="alert alert-warning" role="alert" id="__messageBox" style="display:none;">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">×</span>
							</button>
							<p id="__message"></p>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<h2 class='title-1 m-b-25'></h2>
							<div class="table-responsive table--no-card">
								<?php $this->widget('zii.widgets.grid.CGridView', array(
									'htmlOptions' => array('class' => 'table table-wallet'),
								    'dataProvider'=>$dataProvider,
									'columns' => array(
										array(
								            'name'=>'importo',
											'value'=> '$data->importo." €"',
								        ),
										array(
								            'name'=>'data_scadenza',
											'value'=> 'WebApp::data_it($data->data_scadenza)',
								        ),
										array(
								            'name'=>'status',
											'type' => 'raw',
											'value'=>'WebApp::walletStatus($data->status)',
											'cssClassExpression' => '( $data->status == "complete" ) ? "process" : (( $data->status == "expired" ) ? "denied" : "desc incorso")',
								        ),
										array(
								            'name'=>'id_tipo_pagamento',
											'value'=> 'TipoPagamenti::model()->findByPk($data->id_tipo_pagamento)->description',
								        ),
									)
								));
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
			<?php echo Logo::footer(); ?>
			</div>
		</div>
	</div>
</div>
<?php $this->endWidget(); ?>
