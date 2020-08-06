<div class="login-wrap">
	<div class="login-content">
		<div class="login-logo">
			<?php Logo::login(); ?>
		</div>
		<div class="input-group" id="login-menu">
			<div class="col-md-6 login-login login-active">
				<a class="js-arrow" href="<?php echo Yii::app()->createUrl('site/login'); ?>">
					<i class="fas fa-sign-out-alt"></i>  LOGIN</a>
			</div>
		</div>

		<div class="col-md-12">
			<div class="card border border-primary bg-teal">
				<div class="card-header text-primary">
					<strong class="card-title">Recupero password</strong>
				</div>
				<div class="card-body">
					<div class="alert alert-danger">
						Errore! Non è stato possibile ripristinare la password!
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
