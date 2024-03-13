<?php
namespace app\components;

use Yii;

use app\models\Auth;
use app\models\Users;
use app\models\Privileges;
use yii\helpers\ArrayHelper;
use app\components\Log;
use app\models\Merchants;
use app\models\Stores;

/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class AuthHandler
{
    const PRIVILEGE_ADMINISTRATOR_ID = 1; // privilege_id -> webmaster
    const PRIVILEGE_USER_ID = 2; // USER

    const PROVIDER_SOURCE = 'NAPAY';
    const PROVIDER_NAME = 'Napay';

    // user result from doAuth
    private $userInfo;

    // user Fields in model
    private $userFields;

    public function __construct($userInfo)
    {
        $this->userInfo = $userInfo;
    }

    private function getRolesIdFromCognitoCode(){
        return ArrayHelper::map(Privileges::find()->all(), 'codice_ruolo', 'id');
    }

    private function getMerchantIdByCompanyId($companyId)
    {
        $model = Merchants::find()->byCompanyId($companyId)->one();
        return $model->id ?? null;
    }

    private function getStoreIdByStoreId($storeId)
    {
        $model = Stores::find()->byStoreId($storeId)->one();
        return $model->id ?? null;
    }

    public function handle()
    {
        // echo '<pre>' . print_r($this->userInfo, true);
        // exit;
        $userAttributes = $this->userInfo->data;
        // echo '<pre>' . print_r($attributes, true) . '</pre>'; 
        // exit;
       
        if (!isset(($userAttributes->source))) {
            $message = Yii::t('app', 'Unable to login. The user {user} does not belong to the organization.', [
                'user' => $userAttributes->username,
            ]);
            Yii::$app->getSession()->setFlash('error', $message);
            Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id ?? 'index'), $message);
            return false;
        }

        if (empty($userAttributes->source)) {
            $message = Yii::t('app', 'Unable to login the user {user}. No organization provided by {client}', [
                'user' => $userAttributes->username,
                'client' => self::PROVIDER_NAME,
            ]);
            Yii::$app->getSession()->setFlash('error', $message);
            Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
            return false;
        }

        if (empty($userAttributes->authorities)) {
            $message = Yii::t('app', 'Unable to login the user {user}. No role provided by {client}', [
                'user' => $userAttributes->username,
                'client' => self::PROVIDER_NAME,
            ]);
            Yii::$app->getSession()->setFlash('error', $message);
            Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
            return false;
        }

        if (!isset(($userAttributes->enabled))) {
            $message = Yii::t('app', 'Unable to login the user {user}. Cannot retrieve the user\'s status', [
                'user' => $userAttributes->username,
            ]);
            Yii::$app->getSession()->setFlash('error', $message);
            Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id ?? 'index'), $message);
            return false;
        }

        if (empty($userAttributes->enabled || $userAttributes->enabled == false)) {
            $message = Yii::t('app', 'Unable to login the user {user}. User disabled by Administrator', [
                'user' => $userAttributes->username,
            ]);
            Yii::$app->getSession()->setFlash('error', $message);
            Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id ?? 'index'), $message);
            return false;
        }



        /**
         * This is the model class for table "users".
         *
         * @property int $id
         * @property string $username
         * @property string $email
         * @property string $first_name
         * @property string $last_name
         * @property string $oauth_provider
         * @property string $oauth_uid
         * @property string $authKey
         * @property string $accessToken
         * @property string $jwt
         * @property string $picture
         * @property int $privilege_id
         * @property int $is_active
         * @property int $merchantd_id
         * @property int $store_id
         */

        $session = Yii::$app->session;
        $token_data = $session->get('user_jwt');

        $roles = $this->getRolesIdFromCognitoCode();
        $privilege_id = $roles[$userAttributes->authorities[0]->name] ?? self::PRIVILEGE_USER_ID; // 1 è per il sysadmin
        $picture = (empty($userAttributes->profileImage)) ?  '/bundles/site/images/anonymous.png' : $userAttributes->profileImage;

        $this->userFields = [
            'username' => $userAttributes->username,
            'first_name' => $userAttributes->firstName,
            'last_name' => $userAttributes->lastName,
            'email' => $userAttributes->email,
            'oauth_provider' => $userAttributes->source,
            'oauth_uid' => $userAttributes->id,
            'authKey' => \Yii::$app->security->generateRandomString(),
            'accessToken' => \Yii::$app->security->generateRandomString(),
            'jwt' => $token_data,
            'picture' => $picture,
            'privilege_id' => $privilege_id,
            'is_active' => (int) $userAttributes->enabled ?? 0,
            'merchant_id' => $this->getMerchantIdByCompanyId($userAttributes->companyId), 
            'store_id' => $this->getStoreIdByStoreId($userAttributes->storeId), 
        ];

        // echo '<pre>' . print_r($userAttributes, true) . '</pre>';
        // echo '<pre>' . print_r($this->userFields, true) . '</pre>'; 
        // exit;

        /* @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => self::PROVIDER_SOURCE,
            'source_id' => $this->userFields['oauth_uid'],
        ])->one();

        
        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                /* @var User $user */
                $user = $auth->user;
                if ($this->updateUserInfo($user)) {
                    Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), Yii::t('app','User {user} has logged in.',['user'=> $userAttributes->username]));
                    Yii::$app->user->login($user, Yii::$app->params['user.passwordResetTokenExpire']);
                } else {
                    $message = Yii::t('app', 'The user {user} was blocked by administrator.',['user'=> $userAttributes->username]);
                    Yii::$app->getSession()->setFlash('error', $message);
                    Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
                }
            } else { // signup
                $existingUser = Users::find()
                    ->andWhere(['username' => $this->userFields['username']])
                    ->andWhere(['oauth_provider' => self::PROVIDER_SOURCE])
                    ->one();

                if ($existingUser) {
                    $auth = new Auth([
                        'user_id' => $existingUser->id,
                        'source' => self::PROVIDER_SOURCE,
                        'source_id' => (string) $this->userFields['oauth_uid'],
                    ]);

                    if ($this->updateUserInfo($existingUser) && $auth->save()) {
                        Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), Yii::t('app', 'User {user} has logged in.', ['user' => $existingUser->username]));
                        Yii::$app->user->login($existingUser, Yii::$app->params['user.passwordResetTokenExpire']);
                    } else {
                        $message = Yii::t('app', 'Unable to save {client} account: {errors}.',[
                            'client' => self::PROVIDER_NAME,
                            'errors' => json_encode($auth->getErrors()),
                        ]);
                        Yii::$app->getSession()->setFlash('error', $message);
                        Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
                        
                    }
                } else {
                    // echo '<pre>' . print_r($this->userFields, true) . '</pre>'; 
                    // exit;
                    $user = new Users($this->userFields);

                    $transaction = Users::getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $user->oauth_provider,
                            'source_id' => (string) $user->oauth_uid,
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), Yii::t('app', 'User {user} has logged in.', ['user' => $userAttributes->username]));
                            Yii::$app->user->login($user, Yii::$app->params['user.passwordResetTokenExpire']);
                        } else {
                            $transaction->rollBack();
                            $message = Yii::t('app', 'Unable to save {client} account: {errors}.', [
                                'client' => self::PROVIDER_NAME,
                                'errors' => json_encode($auth->getErrors()),
                            ]);
                            Yii::$app->getSession()->setFlash('error', $message);
                            Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
                        }
                    } else {
                        $transaction->rollBack();
                        $message = Yii::t('app', 'Unable to save user account: {errors}.', [
                            'errors' => json_encode($user->getErrors()),
                        ]);
                        Yii::$app->getSession()->setFlash('error', $message);
                        Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
                    }
                }
            }

        } else { // user already has logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => self::PROVIDER_SOURCE,
                    'source_id' => (string)$this->userFields['oauth_uid'],
                ]);
                if ($auth->save()) {
                    /** @var Users $user */
                    $user = $auth->user;
                    $this->updateUserInfo($user);
                    
                    Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), Yii::t('app', 'Linked {client} account.', ['client' => self::PROVIDER_NAME]));
                    
                    Yii::$app->getSession()->setFlash('success', [
                        Yii::t('app', 'Linked {client} account.', [
                            'client' => self::PROVIDER_NAME
                        ]),
                    ]);
                } else {
                    $message = Yii::t('app', 'Unable to link {client} account: {errors}.', [
                        'client' => self::PROVIDER_NAME,
                        'errors' => json_encode($auth->getErrors()),
                    ]);
                    Yii::$app->getSession()->setFlash('error', $message);
                    Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
                   
                }
            } else { // there's existing auth
                $message = Yii::t('app', 'Unable to link {client} account. There is another user using it', [
                    'client' => self::PROVIDER_NAME,
                ]);

                Yii::$app->getSession()->setFlash('error', $message);
                Log::save(Yii::$app->controller->id, ( Yii::$app->controller->action->id ?? 'index'), $message);
            }
        }
    }

    /**
     * @param Users $user
     */
    private function updateUserInfo(Users $user)
    {
        $user->email = $this->userFields['email'];
        $user->first_name = $this->userFields['first_name'];
        $user->last_name = $this->userFields['last_name'];
        $user->authKey = \Yii::$app->security->generateRandomString();
        $user->accessToken = $this->userFields['accessToken'];
        $user->jwt = $this->userFields['jwt'];
        $user->picture = $this->userFields['picture'];
        $user->privilege_id = $this->userFields['privilege_id'];
        $user->is_active = $this->userFields['is_active'];
        $user->merchant_id = $this->userFields['merchant_id'];
        $user->store_id = $this->userFields['store_id'];
        
        return $user->save();
    }
}
