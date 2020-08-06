<script>
(function ($) {
	// USE STRICT
	"use strict";
	try {
	  //WidgetChart 3
	  var ctx3 = document.getElementById("widgetChart3");
	   $('#token_incassati').text(<?php echo $tokentotal;?>);

	  if (ctx3) {
		ctx3.height = 130;
		var myChart3 = new Chart(ctx3, {
		  type: 'line',
		  data: {
			labels: [
				<?php
					foreach ($labelsToken as $item)
						echo "'".$item."',";
				?>
			],
			type: 'line',
			datasets: [{
			  data: [
				  <?php
				  	foreach ($datasToken as $item)
						echo "'".$item."',";
				  ?>
			  ],
			  label: 'TTS',
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

                  borderWidth: 1
			  },
			  point: {
				radius: 1,
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
