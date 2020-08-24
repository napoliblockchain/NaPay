<?php
$active = ['No','Si'];
$viewName = 'Log';
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-hand-o-right"></i>
						<span class="card-title">Dettagli <?php echo $viewName;?></span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card">
							<?php $this->widget('zii.widgets.CDetailView', array(
								// 'htmlOptions' => array('class' => 'table table-borderless table-striped '),
								'data'=>$model,
								'attributes'=>array(
									'id_log',
									'timestamp',
									array(
										'label'=>Yii::t('model','timestamp'),
										'type'=>'raw',
										'value'=>WebApp::dateLN($model->timestamp),
									),
									'id_user',
									'app',
									'controller',
									'action',
									array(
										'label'=>Yii::t('model','description'),
										'type'=>'raw',
										'value'=>$model->description,
									),
									'remote_address',
									'browser',
									'die',

								),
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
