<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<h2 class='title-1 m-b-25'><small>Modifica</small> <strong>Master Public Key</strong></h2>
					</div>
					<div class="card-body card-block">
						<?php $this->renderPartial('webapp/stores/savempk/_form', array(
							'model'=>$model,
							'asset'=>$asset,
						)); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
