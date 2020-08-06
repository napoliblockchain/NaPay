<?php
/* @var $this UsersController */
/* @var $model Users */

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<h2 class='m-b-25'><small>Abilitazione </small><strong>Autenticazione a 2 Fattori</strong></h2>
					</div>
					<div class="card-body card-block">
						<?php $this->renderPartial('_form2fa', array(
							'model'=>$model,
							'qrCodeUrl'=>$qrCodeUrl,
							'secret'=>$secret
						)); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
