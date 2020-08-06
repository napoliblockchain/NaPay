<div class="col col-lg-12">
	<div class="statistic__item statistic__item--darkgreen">
		<div class="overview__inner" id='vendite'>
			<div class="overview-box clearfix">
				<div class="icon">
					<i class="fa fa-shopping-cart"></i>
				</div>
				<div class="text" style="margin-right:100px;">
					<h2 id='vendite-totali'>0</h2>
					<span>VENDITE</span>
				</div>
			</div>
			<div class="overview-chart">
				<canvas id="widgetChart1"></canvas>
			</div>
		</div>
	</div>
</div>
<?php
// carico la lista ASSET dichiarato nella webapp
// Copiato da cash-register non esiste la lista ASSET
// la imito creando un array btc
$listaAssets = array('BTC'=>'Bitcoin'); //CHtml::listData( $assets, 'ticket' , 'denomination');

//INIZIALIZZO LE VARIABILI
$labels = [];
$labelsToken = [];
$datas=[];
$datasToken=[];
$total_VENDITE=0;
$total_EURO=0;
$tokentotal=0;

foreach ($listaAssets as $index => $asset){
	$total[$index] = 0;
}

//INIZIO L'ITERAZIONE SUL CDataProvider
$iterator = new CDataProviderIterator($dataProvider);
foreach($iterator as $item) {
	#echo "<pre>".print_r($item->status,true)."</pre>";
		if (
			$item->price >0
			&& (
				$item->status == 'paid'
				|| $item->status=='confirmed'
				|| $item->status == 'complete'
				)

		){
			$labels[] = date("d M Y",$item->invoice_timestamp);
			$datas[] = $item->price;

			$total_VENDITE++;
			$total_EURO += $item->price;

			// per ottenere il saldo della coin devo cercare in transactions_info
			// inizializzo i criteri di ricerca
			$criteria=new CDbCriteria();
			$criteria->compare('id_transaction',$item->id_transaction,false);
			$criteria->compare('txCount',1,false);
			$transactionsInfo = TransactionsInfo::model()->findAll($criteria);
			#echo "<pre>".print_r($transactionsInfo,true)."</pre>";
			#exit;

			foreach ($transactionsInfo as $itemInfo){
				$total[$itemInfo->cryptoCode] += $item->btc_price;
			}
		}
}
// echo "<pre>".print_r($total,true)."</pre>";
// exit;

//INIZIO L'ITERAZIONE SUL CDataProviderTokens
// questi dati vengono utilizzati anche da js_token_incassati
$iteratorToken = new CDataProviderIterator($dataProviderTokens);
foreach($iteratorToken as $item) {
	#echo "<pre>".print_r($item->status,true)."</pre>";
	if ($item->token_price >0 && $item->status == 'complete'){
		$labels[] = date("d M Y",$item->invoice_timestamp);
		$datas[] = $item->token_price;
		$tokentotal += $item->token_price;
		$total_VENDITE++;

		//per il js di token
		$labelsToken[] = date("d M Y",$item->invoice_timestamp);
		$datasToken[] = $item->token_price;
	}
}
include (dirname(__FILE__).'/../js/vendite_totali.php');
?>
