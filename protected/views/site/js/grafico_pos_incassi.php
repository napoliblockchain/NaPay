<?php
//INIZIALIZZO LE VARIABILI
$labels = [];
$datas=[];

$totale=[0=>0,1=>0];


//INIZIO L'ITERAZIONE SUL CDataProvider
//BTC
$iterator = new CDataProviderIterator($dataProvider);
foreach($iterator as $item) {
	if ($item->price >0 && ($item->status == 'paid' || $item->status=='confirmed' || $item->status == 'complete' )){
		$totale[0] += $item->price;
		//$btc += $item->btc_price;
		$ar2[date("d M Y - H:i",$item->invoice_timestamp)] = [
			0 => $totale[0],
			//1 => $totale[1],
		];
	}
}

$iterator = new CDataProviderIterator($dataProviderTokens);
foreach($iterator as $item) {
	if ($item->token_price >0 && $item->status == 'complete' && $item->item_desc != 'wallet' ){
		$totale[1] += $item->token_price;

		$ar2[date("d M Y - H:i",$item->invoice_timestamp)] = [
			//0 => $totale[0],
			1 => $totale[1],
		];
	}
}

if (isset($ar2))
	ksort($ar2);
else {
	$ar2=[];
}

#echo "<pre>".print_r($ar2,true)."</pre>";
$totale=[0=>0,1=>0];
foreach ($ar2 as $date => $i){

	if (isset($i[0]))
		$totale[0] = $i[0];

	if (isset($i[1]))
		$totale[1] = $i[1];

	$ar[$date] = [
		0 => $totale[0],
		1 => $totale[1],
	];

}
if (!isset($ar))
	$ar = [];

#echo "<pre>".print_r($ar,true)."</pre>";
#exit;



?>

	<div class="au-card m-b-30">
		<div class="au-card-inner">
			<h3 class="title-2 m-b-40">Vendite</h3>
			<canvas id="sales-chart" height="322" width="644" class="chartjs-render-monitor"></canvas>
		</div>
	</div>

<?php //if (isset($pos_desc)){ ?>
<script>
	try {
		//Sales chart
	    var ctx5 = document.getElementById("sales-chart");
		if (ctx5) {
		  ctx5.height = 300;
		  var myChart = new Chart(ctx5, {
	        type: 'line',
	        data: {
	          labels: [<?php foreach ($ar as $date => $item) echo "'".substr($date,0,11)."',"; ?>],
	          type: 'line',
	          defaultFontFamily: 'Poppins',
	          datasets: [{
	            label: "Bitcoin",
	            data: [<?php foreach ($ar as $date => $item) echo "'".$item[0]."',"; ?>],
	            backgroundColor: 'transparent',
	            borderColor: 'rgba(220,53,69,0.75)',
	            borderWidth: 3,
	            pointStyle: 'circle',
	            pointRadius: 5,
	            pointBorderColor: 'transparent',
	            pointBackgroundColor: 'rgba(220,53,69,0.75)',
	          }, {
	            label: "Token",
	           data: [<?php foreach ($ar as $date => $item) echo "'".$item[1]."',"; ?>],
	            backgroundColor: 'transparent',
	            borderColor: 'rgba(40,167,69,0.75)',
	            borderWidth: 3,
	            pointStyle: 'circle',
	            pointRadius: 5,
	            pointBorderColor: 'transparent',
	            pointBackgroundColor: 'rgba(40,167,69,0.75)',
	          }]
	        },
	        options: {
	          responsive: true,
	          tooltips: {
	            mode: 'index',
	            titleFontSize: 12,
	            titleFontColor: '#000',
	            bodyFontColor: '#000',
	            backgroundColor: '#fff',
	            titleFontFamily: 'Poppins',
	            bodyFontFamily: 'Poppins',
	            cornerRadius: 3,
	            intersect: false,
	          },
	          legend: {
	            display: false,
	            labels: {
	              usePointStyle: true,
	              fontFamily: 'Poppins',
	            },
	          },
	          scales: {
	            xAxes: [{
	              display: true,
	              gridLines: {
	                display: false,
	                drawBorder: false
	              },
	              scaleLabel: {
	                display: false,
	                labelString: 'Month'
	              },
	              ticks: {
	                fontFamily: "Poppins"
	              }
	            }],
	            yAxes: [{
	              display: true,
	              gridLines: {
	                display: false,
	                drawBorder: false
	              },
	              scaleLabel: {
	                display: true,
	                labelString: 'Importo',
	                fontFamily: "Poppins"

	              },
	              ticks: {
	                fontFamily: "Poppins"
	              }
	            }]
	          },
	          title: {
	            display: false,
	            text: 'Normal Legend'
	          }
	        }
	      });
		}
	} catch (error) {
	  console.log(error);
	}

</script>
<?php //} ?>
