<div class="col-sm-6 col-lg-4">
	<!-- <div class="overview-item overview-item--c2">
		<div class="overview__inner" id='wallet_btc'>
			<div class="overview-box clearfix">
				<div class="icon">
					<i class="zmdi zmdi-star"></i>
				</div>
				<div class="text">
					<h2 id='token_incassati'>0</h2>
					<span title="I Token mostrati sono relativi alle sole vendite da POS">Saldo Token</span>
				</div>
			</div>
			<div class="overview-chart">
				<canvas id="widgetChart3"></canvas>
			</div>
		</div>
	</div> -->
	<div class="statistic__item statistic__item--blue">
		<h2 class="number" id='token_incassati'>0</h2>
		<span class="desc" title="I Token mostrati sono relativi alle sole vendite da POS">Saldo Token</span>
		<div class="icon">
			<i class="zmdi zmdi-star"></i>
		</div>
	</div>
</div>
<?php
//
//è tutto già inizializzato e caricato in vendite totali

// //INIZIALIZZO LE VARIABILI
// $labels = [];
// $datas=[];
// $total=0;

//INIZIO L'ITERAZIONE SUL CDataProvider
// $iterator = new CDataProviderIterator($dataProviderTokens);
// foreach($iterator as $item) {
// 	if ($item->token_price >0 && $item->status == 'complete' && $item->item_desc <> 'wallet'){
// 		$labels[] = date("d M Y",$item->invoice_timestamp);
// 		$datas[] = $item->token_price;
// 		$total += $item->token_price;
// 	}
// }
include (dirname(__FILE__).'/../js/token_incassati.php');
?>
