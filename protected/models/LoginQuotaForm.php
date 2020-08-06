<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginQuotaForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
	public $reCaptcha;
	//public $verifyCode;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		if (gethostname()=='CGF6135T'){
			return array(
				// username and password are required
				array('username, password', 'required'),

				// password needs to be authenticated
				array('password', 'authenticateQuota'),

				// username has to be a valid email address
				array('username', 'email'),

				// secret is required
				array('reCaptcha ', 'required'),
			);
		}else{
			return array(
				// username and password are required
				array('username, password', 'required'),

				// password needs to be authenticated
				array('password', 'authenticateQuota'),

				// username has to be a valid email address
				array('username', 'email'),

				// secret is required
				array('reCaptcha ', 'required'),
				array('reCaptcha', 'application.extensions.reCaptcha2.SReCaptchaValidator', 'secret' => Settings::load()->reCaptcha2PrivateKey,'message' => 'The verification code is incorrect.'),
			);
		}
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'username'=>'Email',
			'password'=>'Password'
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 * @param string $attribute the name of the attribute to be validated.
	 * @param array $params additional parameters passed with rule when being executed.
	 */
	public function authenticateQuota($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserQuotaIdentity($this->username,$this->password);
			// if(!$this->_identity->authenticate())
			//  	$this->addError('password','La password e/o l\'email non sono corrette.');
			$this->_identity->authenticateQuota();
			$errorCode = $this->_identity->errorCode;

			switch ($errorCode){
				case UserQuotaIdentity::ERROR_PASSWORD_INVALID:
					$this->addError('password','La password non è corretta.');
					break;

				case UserQuotaIdentity::ERROR_USERNAME_INVALID:
					$this->addError('username','L\'email non è corretta.');
					break;

				case UserQuotaIdentity::ERROR_USERNAME_NOT_ACTIVE:
					$this->addError('password','L\'utente non è abilitato.');
					break;
			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity===null)
		{
			$this->_identity=new UserQuotaIdentity($this->username,$this->password);
			$this->_identity->authenticateQuota();
		}
		if($this->_identity->errorCode===UserQuotaIdentity::ERROR_NONE)
		{
			$duration=3600*24*90; // 90 days
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		}
		else
			return false;
	}
}
