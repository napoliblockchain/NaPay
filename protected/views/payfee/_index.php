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
$settingsNapos = Settings::load();
$BtcPayServerAddress = $settingsNapos->BTCPayServerAddressWebApp; // è il btcpay server della nostra associazione (che è diverso da quella del cliente)
$BtcPayServerIDPOS = 0; //quando faccio il pairing i dati vengono salvati in settingsNapos e la priv e pub key con il codice 0.key 0.pub
$idUser = Yii::app()->user->objUser['id_user'];

//in base al tipo di personalità (fisica/giuridica) seleziono l'importo da pagare per l'iscrizione
if ($users->corporate == 0)
	$amount = $settingsNapos->quota_iscrizione_socio;
else
	$amount = $settingsNapos->quota_iscrizione_socioGiuridico;

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
	var rate = 0;
	$.getJSON('https://apiv2.bitcoinaverage.com/exchanges/bitstamp', function(data) {\n
		rate = data.symbols.BTCEUR.last;
		//console.log(rate);
	});

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
						'url'			: '{$BtcPayServerAddress}',
						'amount'		: '{$amount}',
					    'email'			: '{$customer}',
					    'redirectUrl'	: '{$URLRedirect}',
						'ipnUrl'		: '{$URLIpn}',
						'rate'			: rate,
					},
					dataType: "json",
					success:function(data){
						$('.bitpay-pairing__loading').remove();
						$("input[name='yt0']").show();

						if (data.error){
							$("#__message").show();
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
						$("#__message").show();
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
							$("#__message").show();
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
			<!-- <button class="au-btn au-btn--block au-btn--green m-b-20" id='yt0hidden' type="button" style="display:none;"></button> -->
			<div class="alert alert-warning" id="__message" style="display:none;"></div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<h2 class='title-1 m-b-25'></h2>
				<div class="table-responsive table--no-card m-b-40">
					<?php $this->widget('zii.widgets.grid.CGridView', array(
						//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
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


<?php $this->endWidget(); ?>
