<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Settings;
use app\components\User;
use app\components\Crypt;


/* @var $this yii\web\View */
/* @var $model app\models\Invoices */

$this->title = $model->invoiceId;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Invoices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="invoices-view">
                
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex flex-row">
                            <h5>
                                <?php

                                echo 'ID Transazione: ' . Html::encode($model->invoiceId);

                                // $url = Url::to($model->checkoutLink, true);

                                // echo 'ID Transazione: ' . Html::a(Html::encode($this->title), $url, [
                                //     'title' => Html::encode($this->title),
                                //     // 'class' => 'btn btn-lg btn-secondary',
                                //     'target' => '_blank'
                                // ]);
                                ?>
                            </h5>
                            

                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                // 'id',
                                [
                                    'attribute' => 'merchant.description',
                                    'label' => Yii::t('app', 'Merchant'),
                                ],
                                [
                                    'attribute' => 'store.description',
                                    'label' => Yii::t('app', 'Store'),
                                ],
                                // 'storeId',
                                [
                                    'attribute' => 'pos.appName',
                                    'label' => Yii::t('app', 'Nome POS'),
                                ],
                                // [
                                //     'attribute' => 'pos.sin',
                                //     'label' => Yii::t('app', 'SID'),
                                // ],

                                // 'invoiceId',
                                [
                                    'attribute' => 'createdTime',
                                    // 'createdTime:datetime',
                                    'value' => function ($data) {
                                        return $data->createdTime;
                                        // return Yii::$app->formatter->asDate(($data->createdTime), 'php:d/m/Y');
                                    },
                                    'format' => ['DateTime', 'php:H:i:s d/m/Y'],


                                ],
                                'invoiceType',
                                [
                                    'attribute' => 'amount',
                                    'value' => function ($data) {
                                        return Yii::$app->formatter->asCurrency(($data->amount), $data->currency);
                                    },
                                ],

                                'status',
                                // 'metadata',
                                // 'checkout',
                                // 'receipt',
                                // 'amount',
                                // 'currency',
                                // 'type',
                                // 'checkoutLink:url',
                                // 'createdTime:datetime',
                                // 'expirationTime:datetime',
                                // 'monitoringExpiration',
                                // 'additionalStatus',
                                // 'availableStatusesForManualMarking',
                                // 'archived'
                            ],
                        ]) ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="invoices-index">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <div class="d-flex flex-row">
                            <h3 class="p-1">Dettagli pagamento</h3>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProviderPayments,
                            'columns' => [
                                'paymentMethod',
                                [
                                    'attribute' => 'destination',
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Html::encode($data->destination);
                                    },
                                    'contentOptions' => ['class' => 'text-break'],
                                ],
                                // 'rate',
                                // [
                                //     'attribute' => 'rate',
                                //     'value' => function ($data) use ($model) {
                                //         return Yii::$app->formatter->asCurrency(($data->rate), $model->currency);
                                //     },
                                // ],
                                [
                                    'attribute' => 'Tasso',
                                    'value' => function ($data) {
                                        return Html::encode(\Yii::$app->formatter->asCurrency((float) $data->rate));
                                    },
                                    'format' => 'raw',
                                ],

                                // importo_iniziale = 30750 / (1 - 5/100) = 32368.42€ (approssimato a due decimali)
                                // [
                                //     'attribute' => 'Tasso originale senza spread',
                                //     'value' => function ($data) use ($model) {
                                //         return Html::encode(\Yii::$app->formatter->asCurrency((float) $data->rate / (1 - ($model->store->storesettings->spread / 100))));
                                //     },
                                //     'format' => 'raw',
                                // ],
                                // 'paymentMethodPaid',
                                // 'totalPaid',
                                // 'due',
                                // 'amount',
                                [
                                    'attribute' => 'totalPaid',
                                    'value' => function ($data) {
                                        return Yii::$app->formatter->asDecimal($data->totalPaid, 8);
                                    },
                                    'format' => 'raw',
                                ],
                                // [
                                //     'attribute' => 'due',
                                //     'value' => function ($data) {
                                //         return Yii::$app->formatter->asDecimal($data->due, 8);
                                //     },
                                //     'format' => 'raw',
                                // ],
                                // [
                                //     'attribute' => 'amount',
                                //     'value' => function ($data) {
                                //         return Yii::$app->formatter->asDecimal($data->amount, 8);
                                //     },
                                //     'format' => 'raw',
                                // ],
                                [
                                    'attribute' => 'networkFee',
                                    'value' => function ($data) {
                                        return Yii::$app->formatter->asDecimal($data->networkFee, 8);
                                    },
                                    'format' => 'raw',
                                ],
                                // [
                                //     'attribute' => 'Importo esercente',
                                //     'value' => function ($data) use ($model) {
                                //         $togli_fee = $data->amount - $data->networkFee;
                                //         $togli_percentuale = $togli_fee -  (($togli_fee * $model->store->storesettings->spread / 100));
                                //         return Html::encode(Yii::$app->formatter->asDecimal($togli_percentuale, 8));
                                //     },
                                //     'format' => 'raw',
                                // ],
                                // [
                                //     'attribute' => 'Importo Swag',
                                //     'value' => function ($data) use ($model) {
                                //         $togli_fee = $data->amount - $data->networkFee;
                                //         $percentuale = (($togli_fee * $model->store->storesettings->spread / 100));
                                //         return Html::encode(Yii::$app->formatter->asDecimal($percentuale, 8));
                                //     },
                                //     'format' => 'raw',
                                // ],
                                // 'networkFee',
                                // 'payments',
                                // 'additionalData',

                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>