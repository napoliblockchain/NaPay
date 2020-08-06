<?php
$active = ['No','Si'];
$actionURL = Yii::app()->createUrl('institutes/create');
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-hand-o-right"></i>
						<span class="card-title">Lista Istituti</span>
						<div class="float-right">
							<a href="<?php echo $actionURL;?>">
								<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus"></i></button>
							</a>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">
							<?php $this->widget('zii.widgets.grid.CGridView', array(
								'htmlOptions' => array('class' => 'table table-wallet'),
							   'dataProvider'=>$dataProvider,
								'columns' => array(
									array(
											'name'=>'Email',
											'value'=> 'Users::model()->findByPk($data->id_user)->email',
									),

									array(
											'type'=> 'raw',
							       	'name'=>'description',
											'value' => 'CHtml::link(CHtml::encode($data->description), Yii::app()->createUrl("institutes/view",array("id"=>crypt::Encrypt($data->id_institute))))',
							    ),
									'wallet_address',
									array(
									  'name'=>'',
									  'value' => '',
								   ),
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
