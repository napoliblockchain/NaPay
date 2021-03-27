<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	const ERROR_USERNAME_NOT_ACTIVE = 3;
	const ERROR_USERNAME_NOT_MEMBER = 4;
	const ERROR_USERNAME_NOT_MERCHANT = 5;
	const ERROR_USERNAME_NOT_PAYER = 6;

	private $_id;
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		// CREO IL PRIMO HASH DI UNA PASSWORD
		// $hash = CPasswordHelper::hashPassword($this->password);
		// echo $hash;
		// exit;
		$save = new Save;

		//Creo la query
		$record=Users::model()->findByAttributes(array('email'=>$this->username));
		if($record===null){
			$this->errorCode=self::ERROR_USERNAME_INVALID;
			$save->WriteLog('napay','useridentity','authenticate','Incorrect username: '.$this->username);
		}
		else if(!CPasswordHelper::verifyPassword($this->password,$record->password)){
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
			$save->WriteLog('napay','useridentity','authenticate','Incorrect password for user: '.$this->username);
		}
		else if($record->status_activation_code == 0){
			$this->errorCode=self::ERROR_USERNAME_NOT_ACTIVE;
			$save->WriteLog('napay','useridentity','authenticate','User not active: '.$this->username);
		}
		else
		{
			//altrimenti, prosegue...
			$this->_id=$record->id_user;
			//$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;

			// Carico lo user type e la descrizione e l'assegno all'array di stato objUser
			$UsersType = new UsersType;

			$UserDesc=CHtml::listData($UsersType::model()->findAll(),'id_users_type','desc');
			$UserPrivileges=CHtml::listData($UsersType::model()->findAll(),'id_users_type','status');

			// echo '<pre>'.print_r($UserPrivileges,true).'</pre>';
			// exit;

			// set this flag true if you don't want check payuments
			$tmpFlagDisableCheckPayments = true;
			/*
			*	VERIFICA SE IL SOCIO HA PAGATO LA QUOTA D'ISCRIZIONE
			*/
			// ma se SEi amministratore non fai il controllo
			if ($UserPrivileges[$record->id_users_type] != 20 && $tmpFlagDisableCheckPayments == false){
				$timestamp = time();
				$criteria = new CDbCriteria();
				$criteria->compare('id_user',$record->id_user, false);

				$provider = Pagamenti::model()->Paid()->OrderByIDDesc()->findAll($criteria);
				if ($provider === null){
					$this->errorCode=self::ERROR_USERNAME_NOT_PAYER;
					$save->WriteLog('napay','useridentity','authenticate','User not payer: '.$this->username);
					return !$this->errorCode;
				}else{
					$provider = (array) $provider;
					if (count($provider) == 0)
						$expiration_membership = 1;
					else
						$expiration_membership = strtotime($provider[0]->data_scadenza);
				}
				// scadenza entro il 31 gennaio per provvedere all'iscrizione (se la data_scadenza
				// è al 31 dicembre)
				// temporaneamente posticipato al 28 febbraio
				$expiration_membership += (31+28) *24*60*60;
				if ($expiration_membership <= $timestamp){
					$this->errorCode=self::ERROR_USERNAME_NOT_MEMBER;
					$save->WriteLog('napay','useridentity','authenticate','User not member: '.$this->username);
					return !$this->errorCode;
				}
			}
			$save->WriteLog('napay','useridentity','authenticate','User '.$this->username. ' logged in.');

			$this->setState('objUser', array(
				'id_user' => $record->id_user,
				'name' => $record->name,
				'surname' => $record->surname,
				'email' => $record->email,
				'ruolo' => $UserDesc[$record->id_users_type],
				'privilegi' => $UserPrivileges[$record->id_users_type],
				'facade' => 'dashboard',
			));
		}
		return !$this->errorCode;
	}
}
