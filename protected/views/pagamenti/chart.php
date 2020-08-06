<?php
//INIZIALIZZO LE VARIABILI
$rgba['Paypal'] = ['rgba(220,53,69,0.75)','rgba(220,53,69,0.9)'];
$rgba['Bitcoin'] = ['rgba(40,167,69,0.75)','rgba(40,167,69,0.9)'];
$rgba['Bonifico'] = ['rgba(0, 123, 255, 0.5)','rgba(0, 123, 255, 0.9)'];
$rgba['Contanti'] = ['rgba(0,0,0,0.75)','rgba(0,0,0,0.09)'];
$rgba['Token'] = ['rgba(0,0,69,0.75)','rgba(0,0,69,0.9)'];
$rgba['Esente'] = ['rgba(120,0,69,0.75)','rgba(120,0,69,0.9)'];

#echo "<pre>".print_r($rgba,true)."</pre>";
#exit;

$totale=[0=>0,1=>0];
$ar = array();

//INIZIO L'ITERAZIONE SUL CDataProvider
$iterator = new CDataProviderIterator($dataProvider);
foreach($iterator as $item) {
	if ($item->importo >0 && ($item->status == 'paid' || $item->status == 'complete' )){
		$users = Users::model()->findByPk($item->id_user);

		if (null !== $users && $users->corporate == 0)
			$totale[0] += $item->importo;
		else
			$totale[1] += $item->importo;

		$ar[date("d M Y",strtotime(str_replace('/', '-',($item->data_registrazione))))] = [
			0 => $totale[0],
			1 => $totale[1],
		];

		$tp[TipoPagamenti::model()->findByPk($item->id_tipo_pagamento)->description][] = 1;
	}
}




?>
	<div class="col-lg-5">
		<div class="au-card m-b-30">
			<div class="au-card-inner">
				<h3 class="title-2 m-b-40">Iscrizioni</h3>
				<canvas id="sales-chart" height="322" width="644" class="chartjs-render-monitor"></canvas>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="au-card m-b-30">
			<div class="au-card-inner">
				<h3 class="title-2 m-b-40">Tipo pagamenti</h3>
				<canvas id="barChart" height="322" width="644" class="chartjs-render-monitor"></canvas>
			</div>
		</div>
	</div>




<script>

try {
    //Sales chart
    var ctx = document.getElementById("sales-chart");
    if (ctx) {
      ctx.height = 350;
      var myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: [<?php foreach ($ar as $date => $item) echo "'".$date."',"; ?>],
          type: 'line',
          defaultFontFamily: 'Poppins',
          datasets: [{
            label: "Soci",
            data: [<?php foreach ($ar as $date => $item) echo "'".$item[0]."',"; ?>],
            backgroundColor: 'transparent',
            borderColor: 'rgba(220,53,69,0.75)',
            borderWidth: 3,
            pointStyle: 'circle',
            pointRadius: 5,
            pointBorderColor: 'transparent',
            pointBackgroundColor: 'rgba(220,53,69,0.75)',
          }, {
            label: "Commercianti",
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
            //display: false,
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
  try {
      //bar chart
      var ctx6 = document.getElementById("barChart");
      if (ctx6) {
        ctx6.height = 350;
        var myChart = new Chart(ctx6, {
          type: 'bar',
          defaultFontFamily: 'Poppins',
          data: {
            //labels: ['<?php foreach ($tp as $title => $item) echo $title; ?>'],
            datasets: [
				<?php 	foreach ($tp as $title => $item) {
							echo "{
									label: '".$title."',
									data: [".count($item)."],
									borderColor: '".$rgba[$title][0]."',
									borderWidth: '0',
									backgroundColor: '".$rgba[$title][1]."',
									fontFamily: 'Poppins'
								},";
						}
				?>
            ]
          },
          options: {
            legend: {
              position: 'bottom',
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
