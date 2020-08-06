<?php

/**
 * This is the model class for table "np_users".
 *
 * The followings are the available columns in table 'np_users':
 * @property integer $id_user
 * @property integer $id_users_type
 * @property string $status
 * @property string $email
 * @property string $password
 * @property string $name
 * @property string $surname
 * @property string $activation_code
 * @property integer $status_activation_code
 */
class UsersRegisterForm extends CActiveRecord
{
	//public $verifyCode;
	public $reCaptcha;
	public $repeat_password;

	// variabili per il trattamento del consenso
	public $consenso_statuto;
	public $consenso_privacy;
	public $consenso_termini;
	public $consenso_marketing;
	public $consenso_pos;

	// telefono cellulare per iscrizione gruppo Telegram
	public $telefono;

	public $provincia;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_users';
	}


	//hash della password dopo il validate!
	public function beforeSave() {
    	if ($this->isNewRecord) // <---- the difference
        	$this->password=CPasswordHelper::hashPassword($this->password);

		return true;
 	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, password, name, surname, vat, address, cap, city, country, telefono', 'required'),
			array('consenso_statuto, consenso_privacy, consenso_termini, consenso_marketing, consenso_pos', 'required'),

			array('email', 'unique',  'message'=>'La mail inserita è già presente in archivio.'),
			array('email, password, name, surname', 'length', 'max'=>255),
			array('telefono', 'length', 'max'=>100),

			array('vat','filter','filter'=>'strtoupper'),

			array('consenso_statuto', 'compare', 'compareValue' => true, 'message' => 'Devi confermare di aver letto lo Statuto e di accettarne ogni punto.' ),
			array('consenso_privacy', 'compare', 'compareValue' => true, 'message' => 'Devi confermare di aver letto le nostre "Norme sulla privacy"' ),
			array('consenso_termini', 'compare', 'compareValue' => true, 'message' => 'Devi confermare di accettare le nostre "Condizioni di utilizzo"' ),
			array('consenso_pos', 'compare', 'compareValue' => true, 'message' => 'Devi confermare di accettare i nostri "Termini di utilizzo del POS"' ),

			// secret is required
			array('reCaptcha ', 'required'),
			//array('reCaptcha', 'application.extensions.reCaptcha2.SReCaptchaValidator', 'secret' => Settings::load()->reCaptcha2PrivateKey,'message' => 'The verification code is incorrect.'),

			// verifyCode needs to be entered correctly
			//array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
			//array('password', 'compare', 'compareAttribute'=>'repeat_password'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_user, id_users_type, id_carica, email, password, name, surname, activation_code, status_activation_code, telefono', 'safe', 'on'=>'search'),
		);
	}

	// public function vatstrtoupper(){
	// 	$this->vat = strtoupper($this->vat);
	// 	return $this->vat;
	// }


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_user' => 'Id User',
			'id_users_type' => 'Id Users Type',
			'id_carica' => 'Carica',
			'email' => 'Email',
			'password' => 'Password',
			'name' => 'Nome',
			'surname' => 'Cognome',
			'activation_code' => 'Activation Code',
			'status_activation_code' => 'Stato Attivazione',

			'corporate' => 'Persona Giuridica',
			'denomination' => 'Denominazione',
			'vat' => 'Codice Fiscale/P.Iva',
			'address' => 'Indirizzo',
			'city' => 'Città',
			'country' => 'Stato',
			'cap' => 'Cap',
			'telefono' => 'Telefono',

		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id_user',$this->id_user);
		$criteria->compare('id_users_type',$this->id_users_type);
		$criteria->compare('id_carica',$this->id_carica,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('surname',$this->surname,true);
		$criteria->compare('corporate',$this->corporate,true);
		$criteria->compare('denomination',$this->denomination,true);
		$criteria->compare('vat',$this->vat,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('cap',$this->cap,true);
		$criteria->compare('telefono',$this->telefono,true);
		$criteria->compare('activation_code',$this->activation_code);
		$criteria->compare('status_activation_code',$this->status_activation_code);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
