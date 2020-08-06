<?php
?>

	<div class="au-card m-b-30">
		<div class="au-card-inner">
			<h3 class="title-2 m-b-40">Vendite</h3>
			<canvas id="barChart" height="322" width="644" class="chartjs-render-monitor"></canvas>
		</div>
	</div>



<?php //if (isset($pos_desc)){ ?>
<script>
try {
    //bar chart
    var ctx6 = document.getElementById("barChart");
    if (ctx6) {
      ctx6.height = 300;
      var myChart = new Chart(ctx6, {
        type: 'bar',
        defaultFontFamily: 'Poppins',
        data: {
          labels: [<?php foreach ($ar as $date => $item) echo "'".substr($date,0,11)."',"; ?>],
          datasets: [
            {
              label: "Bitcoin",
              data: [<?php foreach ($ar as $date => $item) echo "'".$item[0]."',"; ?>],
              //borderColor: "rgba(0, 123, 255, 0.9)",
			  borderColor: 'rgba(220,53,69,0.75)',
              borderWidth: "0",
              //backgroundColor: "rgba(0, 123, 255, 0.5)",
			  backgroundColor: 'rgba(220,53,69,0.75)',
              fontFamily: "Poppins"
            },
            {
              label: "Token",
              data: [<?php foreach ($ar as $date => $item) echo "'".$item[1]."',"; ?>],
             // borderColor: "rgba(0,0,0,0.09)",
			 borderColor: 'rgba(40,167,69,0.75)',
              borderWidth: "0",
              //backgroundColor: "rgba(0,0,0,0.07)",
			  backgroundColor: 'rgba(40,167,69,0.75)',
              fontFamily: "Poppins"
            }
          ]
        },
        options: {
          legend: {
            position: 'top',
            labels: {
              fontFamily: 'Poppins'
            }

          },
          scales: {
            xAxes: [{
              ticks: {
                fontFamily: "Poppins"

              }
            }],
            yAxes: [{
              ticks: {
                beginAtZero: true,
                fontFamily: "Poppins"
              }
            }]
          }
        }
      });
    }

  } catch (error) {
    console.log(error);
  }

</script>
<?php //} ?>
