<?php
$urlGetExchangeBalance = Yii::app()->createUrl('backend/getExchangeBalance');
$myWalletScript = <<<JS
var ajax_loader_url = 'css/images/loading.gif';
$(function(){
	$.ajax({
		url:'{$urlGetExchangeBalance}',
		type: "GET",
		dataType: "json",
		beforeSend: function() {
			$('#balance-btc').hide();
			$('#balance-eur').hide();
			$('#balance-btc').after('<div class="__loading1"><img width=20 src="'+ajax_loader_url+'"></div>');
			$('#balance-eur').after('<div class="__loading2"><img width=20 src="'+ajax_loader_url+'"></div>');
		},
		success:function(data){
			if (data.error){
				console.log('error');
				return false;
			}else{
				$('.__loading1').remove();
				$('.__loading2').remove();
				$('#balance-btc').show();
				$('#balance-eur').show();

				$('#balance-btc').text(data.btc);
			    $('#balance-eur').text(data.eur);
			}
		},
		error: function(j){
			console.log('error');
		}
	});
});
JS;
Yii::app()->clientScript->registerScript('myWalletScript', $myWalletScript);
?>
