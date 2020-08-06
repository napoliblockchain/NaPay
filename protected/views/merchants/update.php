<?php
/* @var $this MerchantsController */
/* @var $model Merchants */
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<h2 class='title-1 m-b-25'><small>Modifica</small> <strong>commerciante</strong></h2>
					</div>
					<div class="card-body card-block">
						<?php
							$this->renderPartial('_form', array(
								'model'=>$model,
								'users'=>$users,
								'preferredCoinList'=>$preferredCoinList,
								'settings'=>$settings,
							));
						?>
					</div>
				</div>
			</div>
		</div>
    <?php echo Logo::footer(); ?>
	</div>
</div>
<!-- <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="mediumModalLabel">Conferma</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Sei sicuro di voler generare un nuovo indirizzo?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<button type="button" class="btn btn-primary btn-danger" name='generateNewAddressButton'>Conferma</button>
			</div>
		</div>
	</div>
</div>-->
