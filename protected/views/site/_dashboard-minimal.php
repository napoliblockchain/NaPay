<?php
	$items = (object) [
		0 => (object) [
			'icon'=>'fas fa-book',
			'link'=>'site/dash',
			'descri'=>'Verbali',
			'color'=>'c1',
		],
		1 => (object)[
			'icon'=>'fas fa-credit-card',
			'link'=>'pagamenti/index',
			'descri'=>'Pagamenti',
			'color'=>'c2',
		],
		2 => (object)[
			'icon'=>'fa fa-user',
			'link'=>'users/view&id='.crypt::Encrypt(Yii::app()->user->objUser['id_user']),
			'descri'=>'Account',
			'color'=>'c3',
		],
	];
?>



	<div class="section__content section__content--p30">
		<div class="container-fluid">
			<div class="row m-t-25">
				<?php foreach($items as $item) {?>
				<div class="square overview-item--<?php echo $item->color; ?>" >
					<a href="<?php echo Yii::app()->createUrl($item->link); ?>">
					<div class="overview-item ">
						<div class="overview__inner">
							<div class="overview-box clearfix m-b-40" style="margin-top:40px; paddi ng-bottom: 35px;">
								<div class="icon">
									<i class="<?php echo $item->icon; ?>"></i>
								</div>
								<hr>
								<div class="text">
									<h2><?php echo $item->descri; ?></h2>
								</div>
							</div>
						</div>

					</div>
					</a>
				</div>

				<?php } ?>
			</div>
			<?php echo Logo::footer(); ?>
		</div>
	</div>
