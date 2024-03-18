<?php

namespace app\controllers;

use Yii;
use app\models\Merchants;
use app\models\Stores;
use app\models\Pos;
use app\models\Settings;
use app\models\search\MerchantsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

use app\components\Crypt;
use app\components\User;
use app\components\Log;
use app\models\Users;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\helpers\Json;

/**
 * MerchantsController implements the CRUD actions for Merchants model.
 */
class MerchantsController extends Controller
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
                            'create',
                            'delete',
                            'update',
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
     * Lists all Merchants models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MerchantsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort->defaultOrder = ['description' => SORT_ASC];
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Merchants model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel(Crypt::decrypt($id)),
        ]);
    }

    /**
     * Creates a new Merchants model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Merchants();

        if ($model->load(Yii::$app->request->post())) { // && $model->save()) {
            // echo '<pre>' . print_r($model->attributes, true) . '</pre>';

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Esercente creato correttamente'));
                // Log message
                $message_log = Yii::t('app', 'User {user} has created a new {item}: {itemname}', [
                    'user' => Yii::$app->user->identity->username,
                    'item' => Yii::$app->controller->id,
                    'itemname' => $model->description
                ]);
                Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
                // end log message
                return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
            }
        }

        $users_list = ArrayHelper::map(Users::find()
            ->joinWith('privilege')
            ->where(['privileges.codice_ruolo' => 'ROLE_USER'])
            ->all(), 'id', 'description');

        return $this->render('create', [
            'model' => $model,
            'users_list' => $users_list
        ]);
    }

    /**
     * Updates an existing Merchants model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Esercente aggiornato correttamente'));
            // Log message
            $message_log = Yii::t('app', 'User {user} has updated {item}: {itemname}', [
                'user' => Yii::$app->user->identity->username,
                'item' => Yii::$app->controller->id,
                'itemname' => $model->description
            ]);
            Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
            // end log message
            return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Merchants model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $closed_at = new \DateTime('now');
        $close_date = $closed_at->format('Y-m-d');

        // storicizza l'esercente
        $model = $this->findModel(Crypt::decrypt($id));
        $model->close_date = $close_date;
        $model->historical = 1;
        $model->save();

        // storicizza i negozi collegati al merchant
        $allStores = Stores::find()->byMerchantId($model->id)->all();

        foreach ($allStores as $store){
            $store->close_date = $close_date;
            $store->historical = 1;
            $store->save();

            // storicizza tutti i pos collegati
            $allPos = Pos::find()->byStoreId($store->id)->all();
            foreach ($allPos as $pos) {
                $pos->close_date = $close_date;
                $pos->historical = 1;
                $pos->save();
            }
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
    }

    /**
     * Finds the Merchants model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Merchants the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Merchants::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
