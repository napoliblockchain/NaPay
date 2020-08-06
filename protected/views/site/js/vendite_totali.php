<script>
(function ($) {
	// USE STRICT
	"use strict";
	try {
	  //WidgetChart 1
	  var ctx1 = document.getElementById("widgetChart1");

	  $('#vendite-totali').text(<?php echo $total_VENDITE;?>);

	  if (ctx1) {
		ctx1.height = 130;
		var myChart1 = new Chart(ctx1, {
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
			  backgroundColor: 'rgba(255,255,255,.1)',
			  borderColor: 'rgba(255,255,255,.55)',
			},]
		  },
		  options: {
			maintainAspectRatio: true,
			legend: {
			  display: false
			},
			layout: {
			  padding: {
				left: 0,
				right: 0,
				top: 0,
				bottom: 0
			  }
			},
			responsive: true,
			tooltips: {
			  mode: 'index',
			  titleFontSize: 12,
			  titleFontColor: '#000',
			  bodyFontColor: '#000',
			  backgroundColor: '#fff',
			  //titleFontFamily: 'Montserrat',
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
