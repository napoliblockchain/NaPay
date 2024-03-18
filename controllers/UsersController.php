<?php

namespace app\controllers;

use Yii;
use app\models\Users;
use app\models\Merchants;
use app\models\search\UsersSearch;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;


use app\components\Crypt;
use app\components\User;
use app\components\Log;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\helpers\Json;


/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
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
                    // 'subscribe' => ['POST'],
                    // 'activate' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            // 'index',
                            'view',
                            'change-collapsed',

                            // 'update',
                            // 'activate',
                            
                        ],
                        'allow' => true,
                        'roles' => ['@'] 
                    ],
                   
                ]
            ],
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'index',
                    'update',
                    'disable',
                    'enable',
                    'export',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'update',
                            'disable',
                            'enable',
                            'export',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::can(40);
                        },
                    ],
                ]
            ]
        ];
    }

    /**
     * Cambia menu laterale collapsed/not collapsed
     * @param
     */
    public function actionChangeCollapsed()
    {
        if (isset($_COOKIE['collapsed'])) {
            setcookie('collapsed', "");
        } else {
            setcookie('collapsed', \yii\helpers\Json::encode([
                'body' => 'sidebar-collapse',
            ]));
        }
        return true;
    }

    /**
     * Esporta la selezione in un file excel
     */
    public function actionExport()
    {
        $queryParams = Json::decode($_POST['queryParams']);

        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->sort->defaultOrder = ['last_name' => SORT_ASC];
        $dataProvider->pagination->pageSize = false;
        
        if (User::isAdministrator()) {
            $allModels = $dataProvider->getModels();
            foreach ($allModels as $id => $model) {
                if ($model->privilege->level > User::privilegeLevel()) {
                    unset($allModels[$id]);
                }
            }
            $dataProvider->setModels($allModels);
        }
        $allModels = $dataProvider->getModels();

        // inizializzo la classe Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Attributi da scaricare
        $attributeLabels = [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Nome utente'),
            // 'oauth_provider' => Yii::t('app', 'OAuth Provider'),
            // 'oauth_uid' => Yii::t('app', 'OAuth ID'),
            // 'authKey' => Yii::t('app', 'Auth Key'),
            // 'accessToken' => Yii::t('app', 'Access Token'),
            'first_name' => Yii::t('app', 'Nome'),
            'last_name' => Yii::t('app', 'Cognome'),
            'email' => Yii::t('app', 'Email'),
            // 'picture' => Yii::t('app', 'Picture'),
            'privilege.description' => Yii::t('app', 'Profilo'),
            'merchant.description' => Yii::t('app', 'Esercente'),
            'store.description' => Yii::t('app', 'Negozio'),
            'is_active' => Yii::t('app', 'Abilitato'),
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
        $filename = $date . '-users.xlsx';
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
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        if (User::can(40)){
            $searchModel = new UsersSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->sort->defaultOrder = ['last_name' => SORT_ASC];
            if (User::isAdministrator()){
                $allModels = $dataProvider->getModels();
                foreach ($allModels as $id => $model) {
                    if ($model->privilege->level > User::privilegeLevel()) {
                        unset($allModels[$id]);
                    }
                }
                $dataProvider->setModels($allModels);            
            }

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'queryParams' => Json::encode(Yii::$app->request->queryParams),
            ]);
        } else {
            return $this->redirect(['site/index']);
        }
    }

    /**
     * Displays a single Users model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $merchants_list = ArrayHelper::map(Merchants::find()->all(), 'id', 'description');
        asort($merchants_list);

        return $this->render('view', [
            'model' => $this->findModel(Crypt::decrypt($id)),
            'merchants_list' => $merchants_list,
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));

        // echo "<pre>" . print_r($model->attributes, true) . "</pre>";
        // echo "<pre>" . print_r($_POST, true) . "</pre>";
        // exit;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // echo "<pre>" . print_r($model->attributes, true) . "</pre>";
            // exit;
            // Log message
            $message_log = Yii::t('app', 'User {user} has updated {item}: {itemname}', [
                'user' => Yii::$app->user->identity->username,
                'item' => Yii::$app->controller->id,
                'itemname' => $model->username
            ]);
            Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
            // end log message
            return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
        }

        return $this->redirect(['view', 'id' => $id]);

        
    }

    /**
     * Activates an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionEnable($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));
        $userAttributes = [
            [
                'Name' => 'custom:Status',
                'Value' => (string) 1,
            ],
        ];

        // echo "<pre>".print_r($userAttributes,true)."</pre>";
        // exit;

        $cognito = new CognitoAuthenticator;

        $result = $cognito->update([
            'UserPoolId' => Yii::$app->params['aws']['userpool_id'],
            'ClientId' => Yii::$app->params['aws']['client_id'],
            'Username' => $model->username,
            'UserAttributes' => $userAttributes,
        ]);
        // echo "<pre>".print_r($result,true)."</pre>";exit;
        if ($result) {
            $meta = $result->get('@metadata');
            // echo '<pre>' . print_r($meta, true) . '</pre>';
            if ($meta["statusCode"] == '200') {
                // $this->sendEmailToAdmins($this->attributes);
                $model->is_active = 1;
                $model->save();
                return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
            }
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Activates an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDisable($id)
    {
        $model = $this->findModel(Crypt::decrypt($id));
        $userAttributes = [
            [
                'Name' => 'custom:Status',
                'Value' => (string) 0,
            ],
        ];

        // echo "<pre>".print_r($userAttributes,true)."</pre>";
        // exit;

        $cognito = new CognitoAuthenticator;

        $result = $cognito->update([
            'UserPoolId' => Yii::$app->params['aws']['userpool_id'],
            'ClientId' => Yii::$app->params['aws']['client_id'],
            'Username' => $model->username,
            'UserAttributes' => $userAttributes,
        ]);
        // echo "<pre>".print_r($result,true)."</pre>";exit;
        if ($result) {
            $meta = $result->get('@metadata');
            // echo '<pre>' . print_r($meta, true) . '</pre>';
            if ($meta["statusCode"] == '200') {
                // $this->sendEmailToAdmins($this->attributes);
                $model->is_active = 0;
                $model->save();
                return $this->redirect(['view', 'id' => Crypt::encrypt($model->id)]);
            }
        }

        return $this->redirect(['view', 'id' => $id]);
    }




    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }


}
