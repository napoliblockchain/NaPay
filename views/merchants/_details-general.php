<?php
use kartik\detail\DetailView;
?>

<?= DetailView::widget([
    'model' => $model,
    'condensed' => true,
    'hover' => true,
    'mode' => DetailView::MODE_VIEW,
    'enableEditMode' => FALSE,
    // 'panel' => [
    //     'heading' => Yii::t('app', 'Generale'),
    //     'type' => DetailView::TYPE_INFO,
    // ],
    'labelColOptions' => ['style' => 'width:15%'],
    'valueColOptions' => ['style' => 'width:35%'],
    'attributes' => [
        'description',
        'vatNumber',
        'email:email',
        'swg_companyId',
        'phone',
        'mobile',
        'addressStreet',
        'addressNumberHouse',
        'addressCity',
        'addressZip',
        'addressProvince',
        'addressCountry',

    ],
]) ?>

