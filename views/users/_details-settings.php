<?php

$collapsed_text = [0 => Yii::t('app', 'Sidebar aperta'), 1 => Yii::t('app', 'Sidebar chiusa')];
$collapsed_checked = [1 => null, 0 => 'checked="checked"'];
$collapsed_value = 0;
if (isset($_COOKIE['collapsed'])) {
    $collapsed_value = 1;
}


$options = [
    'spinner' => '<div class="button-spinner spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
    'changeCollapsed' => yii\helpers\Url::to(['/users/change-collapsed']),
];

$this->registerJs(
    "var yiiOptions = " . yii\helpers\Json::htmlEncode($options) . ";",
    yii\web\View::POS_HEAD,
    'yiiOptions'
);
?>

<?php if ($model->id == Yii::$app->user->id) : ?>

    <div class="col-xl-12">
        <div class="card card-info card-outline">
            <div class="card-body box-profile">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input <?= $collapsed_checked[$collapsed_value] ?> type="checkbox" class="custom-control-input" id="pushmenu_collapsed">
                    <label class="custom-control-label" for="pushmenu_collapsed"><?= $collapsed_text[$collapsed_value] ?></label>
                </div>
            </div>
        </div>
    </div>

    <!-- SAVE APP ON HOME -->
    <div class="col-xl-12">
        <div class="card card-info card-outline">
            <div class="card-body box-profile">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="checkbox" class="custom-control-input" id="saveOnDesktop" onclick="js:saveOnDesktop();" >
                    <label class="custom-control-label" for="saveOnDesktop">Salva App su Home</label>
                </div>
            </div>
        </div>
    </div>

    
<?php endif; ?>