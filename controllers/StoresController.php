<?php

namespace app\controllers;

use Yii;
use app\models\Merchants;
use app\models\Storesettings;
use app\models\Stores;
use app\models\Webhooks;
use app\models\search\StoresSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

use app\components\Crypt;
use app\components\User;
use app\components\Log;
use app\components\BTCPayServer;

use app\models\Settings;
use app\models\Pos;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\helpers\Html;

/**
 * StoresController implements the CRUD actions for Stores model.
 */
class StoresController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'export',
                            'create',
                            'update-general',
                            'update-wallet',
                            'update-rates',
                            'update-checkout',
                            'update-criteria',
                            'update-webhook',
                            'delete',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isMerchant();
                        },
                    ],
                    [
                        'actions' => [
                            'index',
                            'view',
                            'create',
                            'update-general',
                            // 'update-wallet',
                            // 'update-rates',
                            // 'update-checkout',
                            // 'update-criteria',
                            // 'update-webhook',
                            'delete',
                            'lista-negozi',
                            'export',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isAdministrator();
                        },
                    ],
                ]
            ]
        ];
    }

    

    /**
     * Lists all Stores models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StoresSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (User::isMerchant()) {
            $dataProvider->query->andwhere(['=', 'stores.merchant_id', User::getMerchantId()]);
        }
        $dataProvider->sort->defaultOrder = ['description' => SORT_ASC];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'queryParams' => Json::encode(Yii::$app->request->queryParams),
        ]);
    }

    /**
     * Displays a single Stores model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));

        $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');
        $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);
        // $client = $BTCPayServer->setStore();

        // recupero il rate da exchange
        $storeId = $model->storesettings->bps_storeid;
        $defaultCurrency = $model->storesettings->defaultPaymentMethod . '_' . $model->storesettings->defaultCurrency;
        $rates = $BTCPayServer->getRates($storeId, $defaultCurrency);
        
        // echo "<pre>" . print_r($rates, true) . "</pre>";exit;
        if (isset($rates['statusCode']) && $rates['statusCode'] == 200){

            $value = (empty($rates->getData()[0]['errors']) ? $rates->getData()[0]['rate'] : $rates->getData()[0]['errors']) . chr(32) . $model->storesettings->defaultCurrency;
        } else {
            $value = 0;
        }

        return $this->render('view', [
            'model' => $model,
            'rates' => $value
        ]);
    }

    /**
     * Creates a new Stores model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() 
    {
        // Verifica che esista l'esercente 
        if (User::isMerchant()){
            $verifyMerchant = Merchants::find()->andWhere(['user_id' => Yii::$app->user->identity->id])->one();

            if (null === $verifyMerchant){
                Yii::$app->session->setFlash('warning', Yii::t('app', 'Prima di poter creare un negozio devi inserire le tue informazioni di esercente.'));
                return $this->redirect(['merchants/create']);
            }
        }

        $model = new Stores();

        if ($model->load(Yii::$app->request->post())) {
            // echo '<pre>' . print_r($model->attributes, true) . '</pre>';;

            // carico il merchant
            $merchant = Merchants::findOne(['id' => $model->merchant_id]);

            // aggiorno il negozio
            $model->email = $merchant->email;
            $model->phone = $merchant->phone;
            $model->mobile = $merchant->mobile;
            $model->addressStreet = $merchant->addressStreet;
            $model->addressNumberHouse = $merchant->addressNumberHouse;
            $model->addressCity = $merchant->addressCity;
            $model->addressZip = $merchant->addressZip;
            $model->addressProvince = $merchant->addressProvince;
            $model->addressCountry = $merchant->addressCountry;

            // echo '<pre>' . print_r($model->attributes, true) . '</pre>';
            // exit;

            // Begin a database transaction
            $transaction = Yii::$app->db->beginTransaction();


            try {
                // carico i settings
                $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');

                if ($model->save()) {
                    // echo "<pre>" . print_r($settings, true) . "</pre>";
                    // exit;
                    if (empty($settings->btcpayApiKey) || empty($settings->btcpayHost)) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('warning', Yii::t('app', 'ERRORE: Chiavi API BTCPAY Server non disponibili'));
                    } else {
                        // PRIMA DI TUTTO GENERO IL MODEL DELLA CONFIGURAZINOE DEL NEGOZIO
                        $storesettings = new Storesettings([
                            'store_id' => $model->id,
                            'bps_storeid' => null, // al momento
                            'defaultCurrency' => 'EUR',
                            'invoiceExpiration' => 900,
                            'displayExpirationTimer' => 300,
                            'monitoringExpiration' => 3600,
                            'speedPolicy' => 'MediumSpeed', // -> 1 confirmation 'HighSpeed' 0,
                            'lightningDescriptionTemplate' => 'Paid to {StoreName} (Order ID: {OrderId})',
                            'paymentTolerance' => 0,
                            'anyoneCanCreateInvoice' => 0,
                            'requiresRefundEmail' => 1,
                            'checkoutType' => 'V1',
                            'receipt' => json_encode([
                                'enabled' => 1,
                                'showQR' => 1,
                                'showPayments' => 1
                            ]),
                            'lightningAmountInSatoshi' => 0,
                            'lightningPrivateRouteHints' => 0,
                            'onChainWithLnInvoiceFallback' => 0,
                            'redirectAutomatically' => 0,
                            'showRecommendedFee' => 1,
                            'recommendedFeeBlockTarget' => 1,
                            'defaultLang' => 'it-IT',
                            'customLogo' => Url::to('@web/bundles/site/images/logopos.png', true),
                            'customCSS' => Url::to('@web/bundles/site/css/checkout.css', true),
                            'htmlTitle' => Html::encode($model->description),
                            'networkFeeMode' => 'Always',
                            'payJoinEnabled' => 0,
                            'lazyPaymentMethods' => 0,
                            'defaultPaymentMethod' => 'BTC',
                            // salvo i criteria in DB, ma non invio request tramite API
                            'paymentMethodCriteria' => json_encode([
                                0 => ['paymentMethod' => 'BTC', 'currencyCode' => 'EUR', 'amount' => 0, 'above' => 1],
                                1 => ['paymentMethod' => 'BTC-LightningNetwork', 'currencyCode' => 'EUR', 'amount' => 0, 'above' => 1],
                                2 => ['paymentMethod' => 'BTC-LNURLPAY', 'currencyCode' => 'EUR', 'amount' => 0, 'above' => 1],
                            ]),
                            // 'derivationScheme' => null, //$settings->derivationScheme,
                            // 'label' => null, //$settings->derivationLabel,
                            // 'accountKeyPath' => null, //$settings->derivationAccountKeyPath,

                        ]);

                        if ($storesettings->save()) {
                            $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);

                            $bpsStore = $BTCPayServer->setStore();

                            // echo "<pre>" . print_r($client, true) . "</pre>";exit;
                            // $client = new \BTCPayServer\Client\Store(Yii::$app->params['btcpay.host'], Yii::$app->params['btcpay.apiKey']);

                            try {
                                $bpsStoreResult = $bpsStore->createStore(
                                    $model->description,
                                    $storesettings->website, //?string $website = null,
                                    $storesettings->defaultCurrency, //string $defaultCurrency = 'USD',
                                    $storesettings->invoiceExpiration, //int $invoiceExpiration = 900,
                                    $storesettings->displayExpirationTimer, // int $displayExpirationTimer = 300,
                                    $storesettings->monitoringExpiration, // int $monitoringExpiration = 3600,
                                    $storesettings->speedPolicy, //string $speedPolicy = 'MediumSpeed',
                                    $storesettings->lightningDescriptionTemplate, //?string $lightningDescriptionTemplate = null,
                                    $storesettings->paymentTolerance, //int $paymentTolerance = 0,
                                    $storesettings->anyoneCanCreateInvoice, //bool $anyoneCanCreateInvoice = false,
                                    $storesettings->requiresRefundEmail, //bool $requiresRefundEmail = false,
                                    $storesettings->checkoutType, //?string $checkoutType = 'V1',
                                    json_decode($storesettings->receipt, true), //?array $receipt = null,
                                    $storesettings->lightningAmountInSatoshi, //bool $lightningAmountInSatoshi = false,
                                    $storesettings->lightningPrivateRouteHints, //bool $lightningPrivateRouteHints = false,
                                    $storesettings->onChainWithLnInvoiceFallback, //bool $onChainWithLnInvoiceFallback = false,
                                    $storesettings->redirectAutomatically, //bool $redirectAutomatically = false,
                                    $storesettings->showRecommendedFee, //bool $showRecommendedFee = true,
                                    $storesettings->recommendedFeeBlockTarget, //int $recommendedFeeBlockTarget = 1,
                                    $storesettings->defaultLang, //string $defaultLang = 'en',
                                    $storesettings->customLogo, //?string $customLogo = null,
                                    $storesettings->customCSS, //?string $customCSS = null,
                                    $storesettings->htmlTitle, //?string $htmlTitle = null,
                                    $storesettings->networkFeeMode, //string $networkFeeMode = 'MultiplePaymentsOnly',
                                    $storesettings->payJoinEnabled, //bool $payJoinEnabled = false,
                                    $storesettings->lazyPaymentMethods, //bool $lazyPaymentMethods = false,
                                    $storesettings->defaultPaymentMethod, //string $defaultPaymentMethod = 'BTC'
                                    // json_decode($storesettings->paymentMethodCriteria), //?array $paymentMethodCriteria = null,
                                );

                                if (null !== $bpsStoreResult) {
                                    $storesettings->bps_storeid = $bpsStoreResult->getId();
                                    $storesettings->save();

                                    /**
                                     * Adesso creiamo il wallet ON-CHAIN 
                                     */
                                    // $paymentMethod = $BTCPayServer->setPaymentMethodOnChain();
                                    // $paymentMethodResult = $paymentMethod->updatePaymentMethod($bpsStoreResult->getId(), 'BTC', [
                                    //     'enabled' => true,
                                    //     'derivationScheme' => $settings->derivationScheme . $settings->derivationAccountKeyPath,
                                    //     'label' => $settings->derivationLabel,
                                    // ]);

                                    /**
                                     * Adesso creiamo il wallet OFF-CHAIN 
                                     */
                                    // $clightningMethod = $BTCPayServer->setPaymentMethodLightningNetwork();
                                    // $clightningMethodResult = $clightningMethod->updatePaymentMethod($bpsStoreResult->getId(), 'BTC', [
                                    //     'enabled' => true,
                                    //     'connectionString' => 'Internal Node'
                                    // ]);

                                    /**
                                     * Adesso impostiamo i WebHooks 
                                     */
                                    // $webHook = $BTCPayServer->setWebhook();

                                    // $webHookResult = $webHook->createWebhook(
                                    //     $bpsStoreResult->getId(),
                                    //     Url::to(['callback/index'], true),
                                    //     null,
                                    //     $settings->webhookSecret,
                                    //     true,
                                    //     true
                                    // );


                                    // if (null !== $paymentMethodResult && null !== $clightningMethodResult && null !== $webHookResult) {
                                    // if (null !== $paymentMethodResult && null !== $webHookResult) {
                                    //     $webHookModel = new Webhooks([
                                    //         'store_id' => $model->id,
                                    //         'bps_storeid' => $bpsStoreResult->getId(),
                                    //         'webhookId' => $webHookResult->getId(),
                                    //         'url' => $webHookResult->getUrl()
                                    //     ]);
                                    //     $webHookModel->save();

                                        // Commit the transaction
                                        $transaction->commit();
                                        Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio creato correttamente'));

                                        // Log message
                                        $message_log = Yii::t('app', 'User {user} has created a new {item}: {itemname}', [
                                            'user' => Yii::$app->user->identity->username,
                                            'item' => Yii::$app->controller->id,
                                            'itemname' => $model->description
                                        ]);
                                        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                                        // end log message

                                        return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
                                    // } else {
                                    //     Yii::$app->session->setFlash('warning', Yii::t('app', 'ERRORE BtcPay: Impossibile salvare il Payment Method'));
                                    //     $transaction->rollBack();
                                    // }
                                } else {
                                    Yii::$app->session->setFlash('warning', Yii::t('app', 'ERRORE BtcPay: Impossibile creare il negozio'));
                                    $transaction->rollBack();
                                }
                            } catch (\Throwable $e) {
                                // echo "Error: " . $e->getMessage();
                                // echo "<pre>" . print_r($bpsStoreResult, true) . "</pre>";
                                // exit;
                                $transaction->rollBack();
                                Yii::$app->session->setFlash('warning', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
                            }
                        } else {
                            $transaction->rollBack();
                            Yii::$app->session->setFlash('warning', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($storesettings->getErrors(), true) . "</pre>"));
                        }
                    }
                } else {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('warning', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($model->getErrors(), true) . "</pre>"));
                }

            } catch (\Exception $e) {
                // Roll back the transaction if an error occurred
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', $e->getMessage());
            }
        }

        $merchants_list = ArrayHelper::map(Merchants::find()->all(), 'id', 'description');
        asort($merchants_list);

        return $this->render('create', [
            'model' => $model,
            'merchants_list' => $merchants_list,
        ]);
    }

    /**
     * Updates an existing Stores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateGeneral($id)
    {
        // echo "<pre>" . print_r($_POST, true) . "</pre>";exit;

        $model = $this->findModel(Crypt::decrypt($id));
        $store = $model->storesettings;

        if ($store->load(Yii::$app->request->post())){
            // echo "<pre>" . print_r($model->attributes, true) . "</pre>";
            // echo "<pre>" . print_r($store->attributes, true) . "</pre>";
            // exit;

            // Begin a database transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($store->save()){
                    $storesettings = [
                        "name" => $model->description,
                        "website" => $store->website,
                        "defaultCurrency" => $store->defaultCurrency,
                        "invoiceExpiration" => (int) $store->invoiceExpiration,
                        "displayExpirationTimer" => $store->displayExpirationTimer,
                        "monitoringExpiration" => $store->monitoringExpiration,
                        "speedPolicy" => $store->speedPolicy,
                        "lightningDescriptionTemplate" => $store->lightningDescriptionTemplate,
                        "paymentTolerance" => $store->paymentTolerance,
                        "anyoneCanCreateInvoice" => $store->anyoneCanCreateInvoice,
                        "requiresRefundEmail" => $store->requiresRefundEmail,
                        "checkoutType" => $store->checkoutType,
                        "receipt" => json_decode($store->receipt, true),
                        "lightningAmountInSatoshi" => $store->lightningAmountInSatoshi,
                        "lightningPrivateRouteHints" => $store->lightningPrivateRouteHints,
                        "onChainWithLnInvoiceFallback" => $store->onChainWithLnInvoiceFallback,
                        "redirectAutomatically" => $store->redirectAutomatically,
                        "showRecommendedFee" => $store->showRecommendedFee,
                        "recommendedFeeBlockTarget" => $store->recommendedFeeBlockTarget,
                        "defaultLang" => $store->defaultLang,
                        'customLogo' => Url::to('@web/bundles/site/images/logopos.png', true),
                        'customCSS' => Url::to('@web/bundles/site/css/checkout.css', true),
                        "htmlTitle" => $store->htmlTitle,
                        "networkFeeMode" => $store->networkFeeMode,
                        "payJoinEnabled" => $store->payJoinEnabled,
                        "lazyPaymentMethods" => $store->lazyPaymentMethods,
                        "defaultPaymentMethod" => $store->defaultPaymentMethod,
                        // "paymentMethodCriteria" => json_decode($store->paymentMethodCriteria)
                    ];

                    $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');

                    // inizializzo la classe BTCPayServer
                    $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);

                    $result = $BTCPayServer->updateStore($store->bps_storeid, $storesettings);

                    // echo "<pre>" . print_r($result, true) . "</pre>";
                    // exit;

                    if (!is_array($result)) {
                        // Commit the transaction
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio aggiornato correttamente'));

                        // Log message
                        $message_log = Yii::t('app', 'User {user} has updated general {item} settings: {itemname}', [
                            'user' => Yii::$app->user->identity->username,
                            'item' => Yii::$app->controller->id,
                            'itemname' => $model->description
                        ]);
                        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                        // end log message

                        return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE BtcPay: Impossibile aggiornare il negozio'));
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: Impossibile aggiornare il negozio'));
                    $transaction->rollBack();
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
            }

        } else {
            // Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($model->getErrors(), true) . "</pre>"));
            Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($store->getErrors(), true) . "</pre>"));

        }

        return $this->render('update', [
            'model' => $model,
            'merchants_list' => ArrayHelper::map(Merchants::find()->all(), 'id', 'description'),
            '_form' => '_form-general',
            'title' => Yii::t('app', 'Update Stores: {name}', [
                'name' => $model->storesettings->bps_storeid,
            ]),
        ]);
    }

    /**
     * Updates an existing Stores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateRates($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));
        $store = $model->storesettings;

        // echo "<pre>" . print_r($_POST, true) . "</pre>";exit;


        if ($store->load(Yii::$app->request->post())) {
            // Begin a database transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $store->save();

                $params = [
                    "spread" => $store->spread,
                    "preferredSource" => $store->preferredSource,
                    "isCustomScript" => false,
                    "effectiveScript" => '',
                ];

                // echo "<pre>" . print_r($params, true) . "</pre>";
                // exit;

                $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');                
                // inizializzo la classe BTCPayServer
                $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);
                
                $result = $BTCPayServer->updateRates($store->bps_storeid, $params);

                if (!is_array($result)) {
                    // Commit the transaction
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio aggiornato correttamente'));

                    // Log message
                    $message_log = Yii::t('app', 'User {user} has updated rates {item} settings: {itemname}', [
                        'user' => Yii::$app->user->identity->username,
                        'item' => Yii::$app->controller->id,
                        'itemname' => $model->description
                    ]);
                    Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                    // end log message
                    return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
                }

            } catch (\Exception $e) {
                // Roll back the transaction if an error occurred
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
            }

        }
        return $this->render('update', [
            'model' => $model,
            'merchants_list' => ArrayHelper::map(Merchants::find()->all(), 'id', 'description'),
            '_form' => '_form-rates',
            'title' => Yii::t('app', 'Update Stores: {name}', [
                'name' => $model->storesettings->bps_storeid,
            ]),
        ]);

    }

    /**
     * Updates an existing Stores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateCheckout($id)
    {
        // echo "<pre>" . print_r($_POST, true) . "</pre>";

        $model = $this->findModel(Crypt::decrypt($id));
        $store = $model->storesettings;

        if ($store->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            $receipt = json_encode([
                'enabled' => (int) $post['Storesettings']['receipt_enabled'],
                'showPayments' => (int) $post['Storesettings']['receipt_showPayments'],
                'showQR' => (int) $post['Storesettings']['receipt_showQR'],
            ],);
            $store->receipt = $receipt;


            // echo "<pre>" . print_r($model->attributes, true) . "</pre>";
            // echo "<pre>" . print_r($store->attributes, true) . "</pre>";
            // exit;

            // Begin a database transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($model->save() && $store->save()) {
                    $storesettings = [
                        "name" => $model->description, // obbligatorio
                        // "website" => $store->website,
                        // "defaultCurrency" => $store->defaultCurrency,
                        // "invoiceExpiration" => (int) $store->invoiceExpiration,
                        // "displayExpirationTimer" => $store->displayExpirationTimer,
                        // "monitoringExpiration" => $store->monitoringExpiration,
                        // "speedPolicy" => $store->speedPolicy,
                        // "lightningDescriptionTemplate" => $store->lightningDescriptionTemplate,
                        // "paymentTolerance" => $store->paymentTolerance,
                        // "anyoneCanCreateInvoice" => $store->anyoneCanCreateInvoice,
                        "requiresRefundEmail" => (int) $store->requiresRefundEmail,
                        // "checkoutType" => $store->checkoutType,
                        "receipt" => json_decode($store->receipt, true),
                        // "lightningAmountInSatoshi" => $store->lightningAmountInSatoshi,
                        // "lightningPrivateRouteHints" => $store->lightningPrivateRouteHints,
                        // "onChainWithLnInvoiceFallback" => $store->onChainWithLnInvoiceFallback,
                        // "redirectAutomatically" => (int) $store->redirectAutomatically,
                        // "showRecommendedFee" => (int) $store->showRecommendedFee,
                        // "recommendedFeeBlockTarget" => $store->recommendedFeeBlockTarget,
                        // "defaultLang" => $store->defaultLang,
                        // "customLogo" => $store->customLogo,
                        // "customCSS" => $store->customCSS,
                        // "htmlTitle" => $store->htmlTitle,
                        // "networkFeeMode" => $store->networkFeeMode,
                        // "payJoinEnabled" => $store->payJoinEnabled,
                        // "lazyPaymentMethods" => $store->lazyPaymentMethods,
                        // "defaultPaymentMethod" => $store->defaultPaymentMethod,
                        // "paymentMethodCriteria" => json_decode($store->paymentMethodCriteria)
                    ];

                 
                    // echo "<pre>" . print_r($storesettings, true) . "</pre>";
                    // exit;

                    $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');

                    // inizializzo la classe BTCPayServer
                    $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);
                    // $client = $BTCPayServer->setStore();
                    $result = $BTCPayServer->updateStore($store->bps_storeid, $storesettings);

                    // echo "<pre>" . print_r($result, true) . "</pre>";
                    // exit;

                    if (!is_array($result)) {
                        // Commit the transaction
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio aggiornato correttamente'));

                        // Log message
                        $message_log = Yii::t('app', 'User {user} has updated checkout {item} settings: {itemname}', [
                            'user' => Yii::$app->user->identity->username,
                            'item' => Yii::$app->controller->id,
                            'itemname' => $model->description
                        ]);
                        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                        // end log message

                        return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE BtcPay: Impossibile aggiornare il negozio'));
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: Impossibile aggiornare il negozio'));
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
            }
        } 
       

        return $this->render('update', [
            'model' => $model,
            'merchants_list' => ArrayHelper::map(Merchants::find()->all(), 'id', 'description'),
            '_form' => '_form-checkout',
            'title' => Yii::t('app', 'Modifica Aspetto del pagamento: {name}', [
                'name' => $model->storesettings->bps_storeid,
            ]),
        ]);
    }

    /**
     * Updates an existing Stores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateCriteria($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));
        $store = $model->storesettings;
        
        if ($store->load(Yii::$app->request->post())) {
            // echo "<pre>" . print_r($_POST, true) . "</pre>";exit;
            $post = Yii::$app->request->post();

            $paymentMethodCriteria = json_encode([
                0 => [
                    'paymentMethod' => 'BTC', 
                    'currencyCode' => $store->defaultCurrency, 
                    'above' => (bool) $post['Storesettings']['paymentMethod_BTC_above'],
                    'amount' => $post['Storesettings']['paymentMethod_BTC_amount'], 
                ],
                1 => [
                    'paymentMethod' => 'BTC-LightningNetwork', 
                    'currencyCode' => $store->defaultCurrency, 
                    'above' => (bool) $post['Storesettings']['paymentMethod_LN_above'],
                    'amount' => $post['Storesettings']['paymentMethod_LN_amount'], 
                ],
                2 => [
                    'paymentMethod' => 'BTC-LNURLPAY', 
                    'currencyCode' => $store->defaultCurrency,
                    'above' => (bool) $post['Storesettings']['paymentMethod_LN_above'],
                    // 'above' => (string) $post['Storesettings']['paymentMethod_LN_above'],
                    'amount' => $post['Storesettings']['paymentMethod_LN_amount'], 
                ],
            ]);
            // echo "<pre>" . print_r($paymentMethodCriteria, true) . "</pre>";
            // exit;
          
            $store->paymentMethodCriteria = $paymentMethodCriteria;


            // echo "<pre>" . print_r($model->attributes, true) . "</pre>";
            // echo "<pre>" . print_r($store->attributes, true) . "</pre>";
            // exit;

            // Begin a database transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($store->save()) {
                    $storesettings = [
                        "name" => $model->description, // obbligatorio
                        // "website" => $store->website,
                        // "defaultCurrency" => $store->defaultCurrency,
                        // "invoiceExpiration" => (int) $store->invoiceExpiration,
                        // "displayExpirationTimer" => $store->displayExpirationTimer,
                        // "monitoringExpiration" => $store->monitoringExpiration,
                        // "speedPolicy" => $store->speedPolicy,
                        // "lightningDescriptionTemplate" => $store->lightningDescriptionTemplate,
                        // "paymentTolerance" => $store->paymentTolerance,
                        // "anyoneCanCreateInvoice" => $store->anyoneCanCreateInvoice,
                        // "requiresRefundEmail" => (int) $store->requiresRefundEmail,
                        // "checkoutType" => $store->checkoutType,
                        // "receipt" => json_decode($store->receipt, true),
                        // "lightningAmountInSatoshi" => $store->lightningAmountInSatoshi,
                        // "lightningPrivateRouteHints" => $store->lightningPrivateRouteHints,
                        // "onChainWithLnInvoiceFallback" => $store->onChainWithLnInvoiceFallback,
                        // "redirectAutomatically" => (int) $store->redirectAutomatically,
                        // "showRecommendedFee" => (int) $store->showRecommendedFee,
                        // "recommendedFeeBlockTarget" => $store->recommendedFeeBlockTarget,
                        // "defaultLang" => $store->defaultLang,
                        // "customLogo" => $store->customLogo,
                        // "customCSS" => $store->customCSS,
                        // "htmlTitle" => $store->htmlTitle,
                        // "networkFeeMode" => $store->networkFeeMode,
                        // "payJoinEnabled" => $store->payJoinEnabled,
                        // "lazyPaymentMethods" => $store->lazyPaymentMethods,
                        // "defaultPaymentMethod" => $store->defaultPaymentMethod,
                        "paymentMethodCriteria" => json_decode($store->paymentMethodCriteria)
                    ];


                    // echo "<pre>" . print_r($storesettings, true) . "</pre>";
                    // exit;

                    $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');

                    // inizializzo la classe BTCPayServer
                    $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);
                    // $client = $BTCPayServer->setStore();

                    $result = $BTCPayServer->updateStore($store->bps_storeid, $storesettings);

                    // echo "<pre>" . print_r($result, true) . "</pre>";
                    // exit;

                    if (!is_array($result)) {
                        // Commit the transaction
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio aggiornato correttamente'));
                        // Log message
                        $message_log = Yii::t('app', 'User {user} has updated criteria {item} settings: {itemname}', [
                            'user' => Yii::$app->user->identity->username,
                            'item' => Yii::$app->controller->id,
                            'itemname' => $model->description
                        ]);
                        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                        // end log message

                        return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE BtcPay: '. $result['message']));
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: Impossibile aggiornare il negozio'));
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
            }
        }
        

        return $this->render('update', [
            'model' => $model,
            'merchants_list' => ArrayHelper::map(Merchants::find()->all(), 'id', 'description'),
            '_form' => '_form-criteria',
            'title' => Yii::t('app', 'Modifica Criteri di pagamento: {name}', [
                'name' => $model->storesettings->bps_storeid,
            ]),
        ]);
    }


    /**
     * Updates an existing Stores model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateWebhook($id)
    {
        $store = $this->findModel(Crypt::decrypt($id));
        $webhookModel = $store->webhook;

        if ($webhookModel->load(Yii::$app->request->post())) {
            // echo "<pre>" . print_r($_POST, true) . "</pre>";;
            // echo "<pre>" . print_r($webhookModel->attributes, true) . "</pre>";
            // exit;

            
            // Begin a database transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($webhookModel->save()) {
                    $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');

                    // inizializzo la classe BTCPayServer
                    $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);

                    /**
                     * Adesso impostiamo i WebHooks 
                     */
                    $webHook = $BTCPayServer->setWebhook();

                    $webHookResult = $webHook->updateWebhook(
                        $store->storesettings->bps_storeid,
                        $webhookModel->url,
                        $webhookModel->webhookId,
                        null
                    );


                    if (null !== $webHookResult) {
                        // Commit the transaction
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio aggiornato correttamente'));
                        // Log message
                        $message_log = Yii::t('app', 'User {user} has updated webhook {item} settings: {itemname}', [
                            'user' => Yii::$app->user->identity->username,
                            'item' => Yii::$app->controller->id,
                            'itemname' => $store->description
                        ]);
                        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                        // end log message

                        return $this->redirect(['view', 'id' => Crypt::encrypt($store->id)]);
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE BtcPay: ' . $result['message']));
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: Impossibile aggiornare il negozio'));
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
            }
        }


        return $this->render('update', [
            'model' => $store,
            'merchants_list' => ArrayHelper::map(Merchants::find()->all(), 'id', 'description'),
            '_form' => '_form-webhook',
            'title' => Yii::t('app', 'Modifica Webhook: {name}', [
                'name' => $store->storesettings->bps_storeid,
            ]),
        ]);
    }
    
    /**
     * Deletes an existing Stores model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $closed_at = new \DateTime('now');
        $close_date = $closed_at->format('Y-m-d');

        // storicizza il negozio
        $model = $this->findModel(Crypt::decrypt($id));
        $model->close_date = $close_date;
        $model->historical = 1;
        $model->save();


        // NON ELIMINO IL NEGOZIO SU BTCPAYWERVER
        // MA LO RICHIAMO DELETED
        $store = $model->storesettings;
        $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');

        $params = [
            "name" => $model->description.'-DELETED',
            "website" => $store->website,
            "defaultCurrency" => $store->defaultCurrency,
            "invoiceExpiration" => (int) $store->invoiceExpiration,
            "displayExpirationTimer" => $store->displayExpirationTimer,
            "monitoringExpiration" => $store->monitoringExpiration,
            "speedPolicy" => $store->speedPolicy,
            "lightningDescriptionTemplate" => $store->lightningDescriptionTemplate,
            "paymentTolerance" => $store->paymentTolerance,
            "anyoneCanCreateInvoice" => $store->anyoneCanCreateInvoice,
            "requiresRefundEmail" => $store->requiresRefundEmail,
            "checkoutType" => $store->checkoutType,
            "receipt" => json_decode($store->receipt, true),
            "lightningAmountInSatoshi" => $store->lightningAmountInSatoshi,
            "lightningPrivateRouteHints" => $store->lightningPrivateRouteHints,
            "onChainWithLnInvoiceFallback" => $store->onChainWithLnInvoiceFallback,
            "redirectAutomatically" => $store->redirectAutomatically,
            "showRecommendedFee" => $store->showRecommendedFee,
            "recommendedFeeBlockTarget" => $store->recommendedFeeBlockTarget,
            "defaultLang" => $store->defaultLang,
            'customLogo' => Url::to('@web/bundles/site/images/logopos.png', true),
            'customCSS' => Url::to('@web/bundles/site/css/checkout.css', true),
            "htmlTitle" => $store->htmlTitle,
            "networkFeeMode" => $store->networkFeeMode,
            "payJoinEnabled" => $store->payJoinEnabled,
            "lazyPaymentMethods" => $store->lazyPaymentMethods,
            "defaultPaymentMethod" => $store->defaultPaymentMethod,
        ];

        // inizializzo la classe BTCPayServer
        $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);

        $result = $BTCPayServer->updateStore($store->bps_storeid, $params);

        // storicizza tutti i pos collegati
        $allPos = Pos::find()->byStoreId($model->id)->all();
        $client = $BTCPayServer->setPos();
        foreach ($allPos as $pos) {
            $pos->close_date = $close_date;
            $pos->historical = 1;
            $pos->save();

            // IL POS DA BTCPAYSERVER VIENE RIMOSSO
            $removePos = $client->removePos($pos->possettings->sin);

        }

        // Log message
        $message_log = Yii::t('app', 'User {user} has deleted {item}: {itemname}', [
            'user' => Yii::$app->user->identity->username,
            'item' => Yii::$app->controller->id,
            'itemname' => $model->description
        ]);
        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
        // end log message

        
        
        
        return $this->redirect(['index']);

        // $transaction = Yii::$app->db->beginTransaction();
        // try {
        //     $store->save();

        //     $settings = (object) ArrayHelper::map(Settings::find()->all(), 'code', 'value');
        //     // inizializzo la classe BTCPayServer
        //     $BTCPayServer = new BTCPayServer($settings->btcpayHost, $settings->btcpayApiKey);
        //     $client = $BTCPayServer->setStore();

        //     $removeStore = $client->removeStore($model->bps_storeid);

        //     if (null !== $removeStore) {
        //         // Commit the transaction
        //         Yii::$app->session->setFlash('success', Yii::t('app', 'Negozio eliminato correttamente'));
        //         $transaction->commit();
        //         return $this->redirect(['index']);
        //     } else {
        //         Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE BtcPay: Impossibile eliminare il negozio'));
        //         $transaction->rollBack();
        //     }
        // } catch (\Exception $e) {
        //     // Roll back the transaction if an error occurred
        //     Yii::$app->session->setFlash('error', Yii::t('app', 'ERRORE: ' . "<pre>" . print_r($e->getMessage(), true) . "</pre>"));
        //     $transaction->rollBack();
        // }
        // return $this->redirect(['view', 'id' => Crypt::encrypt($store->id)]);


    }

    /**
     * return list from jquery dropdown
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionListaNegozi($id)
    {
        $out = [];
        $merchant_id = null;
        // echo '<pre>'.print_r($_POST,true);exit;

        if (isset($_POST['depdrop_all_params'])) {
            $parents = $_POST['depdrop_all_params'];
            if ($parents != null) {
                $merchant_id = (int) $parents['merchant_id'];

                $out = Stores::find()
                    ->select(['id', 'description as name'])
                    ->where(['merchant_id' => $merchant_id, 'historical' => 0])
                    ->asArray()
                    ->all();

                asort($out);

                return Json::encode(['output' => $out, 'selected' => $id]);
            }
        }
        return Json::encode(['output' => $out, 'selected' => '']);
    }

    /**
     * Esporta la selezione in un file excel
     */
    public function actionExport()
    {
        $queryParams = Json::decode($_POST['queryParams']);

        $searchModel = new StoresSearch();
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->pagination->pageSize = false;
        if (User::isMerchant()) {
            $dataProvider->query->andwhere(['=', 'stores.merchant_id', Yii::$app->user->identity->merchant_id]);
        }

        $allModels = $dataProvider->getModels();

        // inizializzo la classe Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Attributi da scaricare
        $attributeLabels = [
            'id' => Yii::t('app', 'ID'),
            'merchant.description' => Yii::t('app', 'Esercente'),

            'description' => Yii::t('app', 'Descrizione'),
            'swg_storeId' => Yii::t('app', 'Codice Negozio'),
            'email' => Yii::t('app', 'Email'),
            'phone' => Yii::t('app', 'Telefono'),
            'mobile' => Yii::t('app', 'Mobile'),
            'addressStreet' => Yii::t('app', 'Indirizzo'),
            'addressNumberHouse' => Yii::t('app', 'Civico'),
            'addressCity' => Yii::t('app', 'Città'),
            'addressZip' => Yii::t('app', 'Zip'),
            'addressProvince' => Yii::t('app', 'Provincia'),
            'addressCountry' => Yii::t('app', 'Nazione'),

            // attributi per settings
            'storesettings.bps_storeid' => Yii::t('app', 'ID Negozio'),
            // 'website' => Yii::t('app', 'Website'),
            'storesettings.defaultCurrency' => Yii::t('app', 'Valuta predefinita'),
            'storesettings.invoiceExpiration' => Yii::t('app', 'Scadenza transazione'),
            'storesettings.displayExpirationTimer' => Yii::t('app', 'Mostra Timer di scadenza'),
            'storesettings.monitoringExpiration' => Yii::t('app', 'Monitoraggio della scadenza'),
            'storesettings.speedPolicy' => Yii::t('app', 'Politica di velocità delle transazioni'),
            // 'lightningDescriptionTemplate' => Yii::t('app', 'Modello di descrizione Lightning'),
            // 'paymentTolerance' => Yii::t('app', 'Tolleranza sul pagamento'),
            // 'anyoneCanCreateInvoice' => Yii::t('app', 'Chiunque può creare una transazione'),
            'storesettings.requiresRefundEmail' => Yii::t('app', 'Richiedi mail per il rimborso'),
            // 'checkoutType' => Yii::t('app', 'Modalità di pagamento'),
            'storesettings.receipt' => Yii::t('app', 'Ricevuta'),
            // 'lightningAmountInSatoshi' => Yii::t('app', 'Importo Lightning in Satoshi'),
            // 'lightningPrivateRouteHints' => Yii::t('app', 'Indicazioni per le route private Lightning'),
            // 'onChainWithLnInvoiceFallback' => Yii::t('app', 'Conferma sulla blockchain con opzione di fallback su fattura LN'),
            // 'redirectAutomatically' => Yii::t('app', 'redirezione automaticamente'),
            // 'showRecommendedFee' => Yii::t('app', 'Visualizza commissione consigliata'),
            // 'recommendedFeeBlockTarget' => Yii::t('app', 'Numero di blocchi consigliato per la commissione'),
            // 'defaultLang' => Yii::t('app', 'Lingua predefinita'),
            // 'customLogo' => Yii::t('app', 'Logo personalizzato'),
            // 'customCSS' => Yii::t('app', 'Css personalizzato'),
            // 'htmlTitle' => Yii::t('app', 'Titolo Html'),
            // 'networkFeeMode' => Yii::t('app', 'Modalità di commissione di rete'),
            // 'payJoinEnabled' => Yii::t('app', 'Pay Join abilitato'),
            // 'lazyPaymentMethods' => Yii::t('app', 'Metodi di pagamento ritardati'),
            // 'defaultPaymentMethod' => Yii::t('app', 'Metodo di pagamento predefinito'),
            'storesettings.paymentMethodCriteria' => Yii::t('app', 'Criteri dei metodi di pagamento'),
            // 'spread' => Yii::t('app', 'Spread'),
            // 'preferredSource' => Yii::t('app', 'Origine predefinita'),
            // 'isCustomScript' => Yii::t('app', 'Is Custom Script'),
            // 'effectiveScript' => Yii::t('app', 'Effective Script'),
            // 'derivationScheme' => Yii::t('app', 'Derivation Scheme'),
            // 'label' => Yii::t('app', 'Label'),
            // 'accountKeyPath' => Yii::t('app', 'Account Key Path'),

            // 'storesettings.receipt_enabled' => Yii::t('app', 'Abilita pagina di ricevuta per transazioni saldate'),
            // 'storesettings.receipt_showPayments' => Yii::t('app', 'Mostra l\'elenco dei pagamenti nella pagina di ricevuta'),
            // 'storesettings.receipt_showQR' => Yii::t('app', 'Mostra il codice QR della ricevuta nella pagina di ricevuta'),
        ];

        // create header
        $x = 0;
        foreach ($attributeLabels as $field => $text) {
            $sheet->setCellValue($this->getCellFromColnum($x) . '1', $text);
            $x++;
        }

        // load rows
        // adesso fare il ciclo sui campi dei titoli...
        $row = 2;
        foreach ($allModels as $n => $model) {
            $col = 0;
            foreach ($attributeLabels as $field => $text) {
                $explode = explode('.', $field);

                if (!isset($explode[1])) {
                    $writeText = $model->$field;
                } else {
                    $submodel = $explode[0];
                    $field = $explode[1];

                    // echo "<pre>" . print_r($model->$submodel, true) . "</pre>";;

                    $writeText = $model->$submodel->$field ?? '';
                }
                $sheet->setCellValue($this->getCellFromColnum($col) . $row, trim($writeText ?? ''), null);
                $col++;
            }
            $row++;
        }

        // output the file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $date = date('Y/m/d H:i:s', time());
        $filename = $date . '-stores.xlsx';
        $response = Yii::$app->getResponse();
        $headers = $response->getHeaders();
        $headers->set('Content-Type', 'application/vnd.ms-excel');
        $headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $headers->set('Cache-Control: max-age=0');
        ob_start();
        $writer->save("php://output");
        $content = ob_get_contents();
        ob_clean();
        return $content;
    }

    private function getCellFromColnum($colNum)
    {
        return ($colNum < 26 ? chr(65 + $colNum) : chr(65 + floor($colNum / 26) - 1) . chr(65 + ($colNum % 26)));
    }

    /**
     * Finds the Stores model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Stores the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stores::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested model does not exist.'));
    }
}
