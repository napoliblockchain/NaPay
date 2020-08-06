<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-list-alt"></i>
						<span class="card-title">Log</span>

					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">
							<?php $this->widget('zii.widgets.grid.CGridView', array(
								//'htmlOptions' => array('class' => 'table table-wallet'),
							    'dataProvider'=>$dataProvider,
								'columns' => array(
									array(
            				'name'=>'timestamp',
      							'type' => 'raw',
										'value' => 'CHtml::link(WebApp::dateLN($data->timestamp), Yii::app()->createUrl("log/view",["id"=>$data->id_log]) )',
                  ),
									array(
            				            'name'=>'id_user',
            							'type' => 'raw',
                                        'value' => '$data->id_user',
                                    ),
									array(
            				            'name'=>'app',
            							'type' => 'raw',
                                        'value' => '$data->app',
                                    ),
									array(
            				            'name'=>'controller',
            							'type' => 'raw',
                                        'value' => '$data->controller',
                                    ),
									array(
            				            'name'=>'action',
            							'type' => 'raw',
                                        'value' => '$data->action',
                                    ),
									array(
            				            'name'=>'description',
            							'type' => 'raw',
                                        'value' => '$data->description',
                                    ),
									array(
            				            'name'=>'die',
                                        'value' => '$data->die',
                                    ),



									// [
									// 'value'=>''
									// ],
								)
							));
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
