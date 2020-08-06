<?php
if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] == 20)
	$settings=Settings::load();
else
	$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);

if (	isset($settings->exchange_key)
	&&  isset($settings->exchange_secret)
	&&  $settings->exchange_key <> ''
	&&  $settings->exchange_secret <> ''
	&&  $settings->exchange_key <> 0
	&&  $settings->exchange_secret <> 0
){
?>

<div class="col-sm-6 col-lg-4">
	<div class="statistic__item statistic__item--red">
		<h2 class="number">
			<table class="table table-top-campaign">
				<tr>
					<td><span class="desc"><i class='fa fa-euro-sign'></i></span></td>
					<td>
						<h3 class="text-light" id='balance-eur'>0</h2>
					</td>
				</tr>
				<tr>
					<td><span class="desc"><i class='fab fa-btc'></i></span></td>
					<td><h3 class="text-light" id='balance-btc'>0</h2></td>
				</tr>
			</table>
		</h2>
		<span class="desc">Conto exchange</span>
		<div class="icon">
			<i class="zmdi zmdi-balance"></i>
		</div>
	</div>
</div>
<?php include (dirname(__FILE__).'/../js/exchangebalance.php'); } ?>
