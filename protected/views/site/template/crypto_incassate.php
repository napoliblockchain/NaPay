<?php
$backgroundColor = [
	'BTC'=>'orange',
	'LTC'=>'gray',
	'DASH'=>'teal',
	'IOTA'=>'indigo',
	'ETH'=>'blue',
	'XXX'=>'green',
	'XRP'=>'red',
	'XXX'=>'gray-dark',
	'BCH'=>'purple',
];



foreach ($total as $asset => $assetValue){
	//if ($assetValue>0){
		?>
		<div class="col-sm-6 col-lg-4">
			<div class="statistic__item statistic__item--<?php echo $backgroundColor[$asset]; ?>">
				<h2 class="number" id='<?php echo $asset; ?>_incassati'>0</h2>
				<span class="desc">Saldo <?php echo $listaAssets[$asset]; ?></span>
				<div class="icon">
					<img style="z-index: -1; opacity:0.35;" width="200" src='<?php echo Yii::app()->baseUrl . '/css/cryptoicon/'.strtolower($asset).'.svg'; ?>' />
				</div>
			</div>
		</div>
		<script>
			  $('#<?php echo $asset; ?>_incassati').text(<?php echo $assetValue;?>);
		</script>
		<?php
	//}
}




?>

<?php //include (dirname(__FILE__).'/../js/bitcoin_incassati.php'); ?>
