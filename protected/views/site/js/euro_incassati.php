<?php
// //INIZIALIZZO LE VARIABILI
// $labels = [];
// $datas=[];
// $total=0;
//
// //INIZIO L'ITERAZIONE SUL CDataProvider
// $iterator = new CDataProviderIterator($dataProvider);
// foreach($iterator as $item) {
// 	if ($item->status == 'confirmed' || $item->status == 'complete' || $item->status == 'paid'){
// 		$labels[] = date("d M Y",$item->invoice_timestamp);
// 		$datas[] = $item->price;
// 		$total += $item->price;
// 	}
// }
#echo "<pre>".print_r($datas,true)."</pre>";
?>
<script>
(function ($) {
	// USE STRICT
	"use strict";
	try {
	  //WidgetChart 2
	  var ctx21 = document.getElementById("widgetChart21");
	  $('#euro_incassati').text(<?php echo $total_EURO;?>);

	  if (ctx21) {
		ctx21.height = 130;
		var myChart21 = new Chart(ctx21, {
		  type: 'line',
		  data: {
			labels: [
				<?php
					foreach ($labels as $item)
						echo "'".$item."',";
				?>
			],
			type: 'line',
			datasets: [{
			  data: [
				  <?php
				  	foreach ($datas as $item)
						echo "'".$item."',";
				  ?>
			  ],
			  label: '€',
			  backgroundColor: 'transparent',
		      borderColor: 'rgba(255,255,255,.55)',
			},]
		  },
		  options: {

	          maintainAspectRatio: false,
	          legend: {
	            display: false
	          },
	          responsive: true,
	          tooltips: {
	            mode: 'index',
	            titleFontSize: 12,
	            titleFontColor: '#000',
	            bodyFontColor: '#000',
	            backgroundColor: '#fff',
	            // titleFontFamily: 'Montserrat',
	            // bodyFontFamily: 'Montserrat',
	            cornerRadius: 3,
	            intersect: false,
	          },
	          scales: {
	            xAxes: [{
	              gridLines: {
	                color: 'transparent',
	                zeroLineColor: 'transparent'
	              },
	              ticks: {
	                fontSize: 2,
	                fontColor: 'transparent'
	              }
	            }],
	            yAxes: [{
	              display: false,
	              ticks: {
	                display: false,
	              }
	            }]
	          },
	          title: {
	            display: false,
	          },
	          elements: {
	            line: {
	              tension: 0.00001,
	              borderWidth: 1
	            },
	            point: {
	              radius: 4,
	              hitRadius: 10,
	              hoverRadius: 4
	            }
	          }
	        }
	      });
	    }

	} catch (error) {
	  console.log(error);
	}
})(jQuery);
</script>
