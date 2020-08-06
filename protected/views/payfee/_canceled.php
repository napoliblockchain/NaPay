

			<div class="col-md-12">
				<div class="card border border-primary bg-teal">
					<div class="card-header text-primary">
						<strong class="card-title">PayPal</strong>
					</div>
					<div class="card-body">
						<div class="alert alert-warning">
							Il pagamento è stato annullato.
						</div>
					</div>
					<div class="card-footer">
						<?php
							$returnURL = Yii::app()->createUrl('payfee/index');
						?>
						<a href="<?php echo $returnURL;?>" target="_self">
							<button type="button" class="btn btn-primary">Indietro</button>
						</a>
					</div>

				</div>
			</div>




		<div class="row">
			<div class="col-lg-12">
				<h2 class='title-1 m-b-25'></h2>
				<div class="table-responsive table--no-card m-b-40">
					<?php $this->widget('zii.widgets.grid.CGridView', array(
						//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
						'htmlOptions' => array('class' => 'table table table-borderless table-data3'),
					    'dataProvider'=>$dataProvider,
						'columns' => array(
							array(
					            'name'=>'importo',
								'value'=> '$data->importo." €"',
					        ),
							array(
					            'name'=>'data_scadenza',
								'value'=> 'WebApp::data_it($data->data_scadenza)',
					        ),
							array(
					            'name'=>'status',
								'type' => 'raw',
								'value'=>'WebApp::walletStatus($data->status)',
								'cssClassExpression' => '( $data->status == "complete" ) ? "process" : (( $data->status == "expired" ) ? "denied" : "desc incorso")',
					        ),
							array(
					            'name'=>'id_tipo_pagamento',
								'value'=> 'TipoPagamenti::model()->findByPk($data->id_tipo_pagamento)->description',
					        ),
						)
					));
					?>
				</div>
			</div>
		</div>
		
