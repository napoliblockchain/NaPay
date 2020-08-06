<?php
$this->pageTitle=Yii::app()->name . ' - Cookies info';
?>
<style>
ul {
  list-style-type: none;
  margin-left: 20px;
}
</style>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <strong class="card-title mb-3">INFORMATIVA SUI COOKIES</strong>
        </div>
        <div class="card-body">
            <div class="typo-articles">
                <?php echo $informativa_cookies; ?>
            </div>
        </div>
    </div>
    <?php echo Logo::footer(); ?>
</div>
