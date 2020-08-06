<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>


		<div class="col-md-12">
			<div class="card border border-primary bg-teal">
				<div class="card-header text-primary">
					<strong class="card-title">Recupero password</strong>
				</div>
				<div class="card-body">
					<div class="alert alert-danger">
						Password salvata! Effettuare il login.
					</div>
				</div>
				<div class="card-footer">
					<a class="js-arrow" href="<?php echo Yii::app()->createUrl('site/login'); ?>">
						<button class="btn btn-primary">
							<i class="fas fa-sign-out-alt"></i>  LOGIN
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
