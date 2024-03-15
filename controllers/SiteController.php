<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\forms\LoginForm;
use app\models\forms\SignupForm;
use app\models\Users;
use app\models\UserConsensus;

use app\components\Log;

use app\models\search\MerchantsSearch;
use app\models\search\StoresSearch;
use app\models\search\PosSearch;
use app\models\search\InvoicesSearch;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout','dashboard'],
                'rules' => [
                    [
                        'actions' => ['logout','dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            throw new \Exception('You are not allowed to access this page');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['get','post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'layout' => Yii::$app->user->isGuest ? 'login' : 'main',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // // esercenti
        // $searchMerchants = new MerchantsSearch();
        // $dataMerchants = $searchMerchants->search(Yii::$app->request->queryParams);
        // $dataMerchants->query->andwhere(['=', 'historical', 0]);
        // if (User::isSenior() || User::isJunior()) {
        //     $dataMerchants->query->andwhere(['=', 'id', Yii::$app->user->identity->merchant_id]);
        // }
        // $dataMerchants->pagination->pageSize = 5;
        // $dataMerchants->sort->defaultOrder = ['description' => SORT_ASC];

        // // negozi
        // $searchStores = new StoresSearch();
        // $dataStores = $searchStores->search(Yii::$app->request->queryParams);
        // $dataStores->query->andwhere(['=', 'stores.historical', 0]);
        // if (User::isJunior()) {
        //     $dataStores->query->andwhere(['=', 'stores.id', Yii::$app->user->identity->store_id]);
        // }
        // $dataStores->pagination->pageSize = 5;
        // $dataStores->sort->defaultOrder = ['description' => SORT_ASC];

        // // pos
        // $searchPos = new PosSearch();
        // $dataPos = $searchPos->search(Yii::$app->request->queryParams);
        // $dataPos->query->andwhere(['=', 'pos.historical', 0]);
        // if (User::isSenior()) {
        //     $dataPos->query->andwhere(['=', 'pos.merchant_id', Yii::$app->user->identity->merchant_id]);
        // }
        // if (User::isJunior()) {
        //     $dataPos->query->andwhere(['=', 'pos.store_id', Yii::$app->user->identity->store_id]);
        // }
        // $dataPos->pagination->pageSize = 5;
        // $dataPos->sort->defaultOrder = ['appName' => SORT_ASC];


        // // invoices
        // $searchInvoices = new InvoicesSearch();
        // $dataInvoices = $searchInvoices->search(Yii::$app->request->queryParams);
        // $dataInvoices->query->andwhere(['=', 'invoices.archived', 0]);
        // $dataInvoices->pagination->pageSize = 5;
        // if (User::isSenior()) {
        //     $dataInvoices->query->andwhere(['=', 'invoices.merchant_id', Yii::$app->user->identity->merchant_id]);
        // }
        // if (User::isJunior()) {
        //     $dataInvoices->query->andwhere(['=', 'invoices.store_id', Yii::$app->user->identity->store_id]);
        // }
        // $dataInvoices->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('index', [
            // 'dataMerchants' => $dataMerchants,
            // 'dataStores' => $dataStores,
            // 'dataPos' => $dataPos,
            // 'dataInvoices' => $dataInvoices,
        ]);
        
    }

    /**
     * phpinfo action.
     */
    public function actionPhpinfo()
    {
        return $this->render('phpinfo');
    }

    
    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }

        /** 
         * verifico se esiste almeno 1 utente altrimenti vai a registrazione
         */
        $test_user = Users::findOne(1);
        if (null === $test_user) {
            return $this->redirect(['site/signup']);
        }

        $this->layout = 'login';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['site/index']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        // Log message
        $message_log = Yii::t('app', 'User {user} has logged out.', [
            'user' => Yii::$app->user->identity->username,
        ]);
        Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
        // end log message

        Yii::$app->user->logout();
        return $this->redirect(['site/login']);

        // return $this->goHome();
    }

    /**
     * Signup action.
     *
     * @return Response|string
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }

        /** 
         * verifico se esiste almeno 1 utente, nel caso lo rispedisco a login
         */
        // $test_user = Users::findOne(1);
        /** TODO: RIMETTERE IL TEST PER NON PERMETTERE REGISTRAZIONE UTENTI */
        // if (null !== $test_user) {
        //     $this->redirect(['site/login']);
        // }

        $this->layout = 'login';

        $model = new SignupForm();
        $consensus = new UserConsensus();


        $post = Yii::$app->request->post();
        // echo "<pre>".print_r($post,true)."</pre>";exit;

        if (isset($post['SignupForm'])) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->signup()) {
                    Yii::$app->session->setFlash('success','Utente registrato con successo');
                    return $this->redirect(['site/index']);
                }
            } else {
                $p = '<p>C\'è stato un errore.</p>';
                $errors = $model->getErrors();
                foreach ($errors as $id => $error) {
                    // echo "<pre>".print_r($error,true)."</pre>";exit;
                    foreach ($error as $e)
                        $p .= '<p>' . $e . '</p>';
                }
                Yii::$app->session->setFlash('error', $p);
            }
        }

        // $model->password = '';
        return $this->render('signup', [
            'model' => $model,
            'consensus' => $consensus,
        ]);
    }

    
}
