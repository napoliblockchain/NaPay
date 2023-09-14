<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Users;
use app\components\AuthHandler;


/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $otpCode;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'otpCode'], 'required'],
            // password is validated by validatePassword()
            ['otpCode', 'validateFields'],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Nome utente'),
            'password' => Yii::t('app', 'Password'),
            'otpCode' => Yii::t('app', 'Codice OTP'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateFields($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user) {
                $this->addError($attribute, Yii::t('app','Nome utente, password o codice OTP errati.'));
                return false;
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            // EFFETTUA L'ACCESSO utente
            $this->onAuthSuccess($this->_user);
            return true; // !!! don't touch it
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Users::doAuth($this->username, $this->password, $this->otpCode);
        }

        return $this->_user;
    }

    public function onAuthSuccess($userInfo)
    {
        (new AuthHandler($userInfo))->handle();
    }
}
