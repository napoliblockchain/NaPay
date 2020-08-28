
<?php

/**
 * This is the model class for table "np_settings".
 *
 * The followings are the available columns in table 'np_settings':
 * @property integer $id_exchanges
 * @property string $denomination
 * @property string $sshhost
 * @property string $sshuser
 * @property string $sshpassword
 *
 */
class SettingsWebappForm extends CFormModel
{
	//Encrypted File System Storage key
	public $fileSystemStorageKey;

	//Google reCaptcha2
	public $reCaptcha2PublicKey;
	public $reCaptcha2PrivateKey;

	//SOCIALS
	public $GoogleOauthClientId;
	public $GoogleOauthClientSecret;
	public $facebookAppID;
	public $facebookAppVersion;
	public $telegramBotName;
	public $telegramToken;

	//Exchange
	public $id_exchange;
	public $exchange_secret;
	public $exchange_key;
	public $only_for_bitstamp_id;

	//Associazione
	// public $association_percent;
	// public $association_receiving_address;
	public $quota_iscrizione_socio;
	public $quota_iscrizione_socioGiuridico;

	//POA TOKEN
	public $poa_url;
	public $poa_port;
	public $poa_expiration;
	public $poa_contractAddress;
	public $poa_abi;
	public $poa_bytecode;
	public $poa_chainId;
	public $poa_blockexplorer;
	public $poa_sealerAccount;
	public $poa_sealerPrvKey;

	// store Associazione per ricevere pagamenti iscrizione in crypto
	public $id_store;
	public $store_denomination;
	public $bps_storeid;
	public $id_gateway;
	public $blockchainAddress;
	public $blockchainAsset;

	//sin per pairing con BTCPayServer
	public $pos_denomination;
	public $pos_sin;
	public $pos_pairingCode;

	//server host
	public $sshhost;
	public $sshuser;
	public $sshpassword;
	// public $rpchost;
	// public $rpcport;

	//varie
	public $step;
	public $version;

	//GDPR
	public $gdpr_titolare;
	public $gdpr_vat;
	public $gdpr_address;
  public $gdpr_city;
  public $gdpr_country;
  public $gdpr_cap;
	public $gdpr_telefono;
	public $gdpr_fax;
	public $gdpr_email;
	public $gdpr_dpo_denomination;
	public $gdpr_dpo_email;
	public $gdpr_dpo_telefono;

	//VAPID keys for Push messages
	public $VapidPublic;
	public $VapidSecret;

	//PAYPAL
	public $PAYPAL_CLIENT_ID;
	public $PAYPAL_CLIENT_SECRET;
	public $PAYPAL_MODE;


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('BTCPayServerAddress', 'required'),
			array('id_store, id_gateway, id_exchange, poa_expiration', 'numerical', 'integerOnly'=>true),
			array('quota_iscrizione_socio, quota_iscrizione_socioGiuridico, poa_chainId', 'numerical', 'integerOnly'=>false),
			array('store_denomination, blockchainAddress, bps_storeid, exchange_secret, exchange_key, poa_contractAddress', 'length', 'max'=>250),
			array('pos_pairingCode, only_for_bitstamp_id, poa_port', 'length', 'max'=>10),
			array('poa_url,version', 'length', 'max'=>50),
			array('blockchainAsset, sin,token,sshhost,sshuser,sshpassword,poa_blockexplorer,poa_sealerAccount,poa_sealerPrvKey', 'length', 'max'=>1000),
			array('poa_abi,poa_bytecode', 'length', 'max'=>15000),
			array('gdpr_titolare, gdpr_address, gdpr_city, gdpr_country, gdpr_cap, gdpr_dpo_denomination', 'length', 'max'=>250),
			array('pos_sin, pos_denomination, gdpr_vat, gdpr_telefono, gdpr_fax, gdpr_email, gdpr_dpo_email, gdpr_dpo_telefono', 'length', 'max'=>50),
			array('VapidPublic,VapidSecret,reCaptcha2PublicKey,reCaptcha2PrivateKey', 'length', 'max'=>150),
			array('PAYPAL_CLIENT_ID,PAYPAL_CLIENT_SECRET,PAYPAL_MODE', 'length', 'max'=>150),
			array('fileSystemStorageKey', 'length', 'max'=>150),
			array('GoogleOauthClientId,GoogleOauthClientSecret,facebookAppID,facebookAppVersion,telegramBotName,telegramToken', 'length', 'max'=>150),

		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_setting' => 'Id Impostazioni',

			'id_exchange' => 'Id Exchange',
			'exchange_secret' => 'Chiave Segreta Exchange',
			'exchange_key' => 'Chiave Pubblica Exchange',
			'only_for_bitstamp_id'=>'Bitstamp ID Api',

			//
			'store_denomination' => 'Denominazione',
			'bps_storeid' => 'ID Negozio',
			'id_gateway' => 'Seleziona il Gateway',
			'blockchainAddress' => 'Seleziona il Server Blockchain',
			'blockchainAsset' => 'Asset Disponibili',

			'pos_denomination' => 'Descrizione POS',
			'pos_sin' =>'Sin',
			'pos_pairingCode'=>'Pairing Code',

			//
			'poa_url'=>'URL del nodo POA',
			'poa_port'=>'Porta del nodo POA',
			'poa_contractAddress'=>'Indirizzo dello Smart Contract',
			'poa_abi'=>'Smart Contract ABI',
			'poa_bytecode'=>'Smart Contract bytecode',
			'poa_expiration'=>'Il pagamento scade se l\'ammontare totale non è stato pagato dopo xxx minuti',
			'poa_chainId'=>'Chain Id',
			'poa_blockexplorer'=>'URL Block Explorer',
			'poa_sealerAccount'=>'Indirizzo Nodo Sealer',
			'poa_sealerPrvKey'=>'Private Key Nodo Sealer',

			//
			'version'=>'Versione applicazione',
			'quota_iscrizione_socio'=>'Quota Iscrizione (Persona Fisica)',
			'quota_iscrizione_socioGiuridico'=>'Quota Iscrizione (Persona Giuridica)',
			'sin'=>'SIN Pairing Associazione',
			'token'=>'Token Pairing',

			'sshhost' => 'Indirizzo tcp/ip Host VPS',
			'sshuser' => 'Utente ssh',
			'sshpassword'=>'Password',

			'gdpr_titolare' =>'Titolare del trattamento (associazione)',
			'gdpr_address' =>'Indirizzo',
			'gdpr_vat' =>'Codice Fiscale',
			'gdpr_cap' =>'Cap',
			'gdpr_city' =>'Città',
			'gdpr_country' =>'Stato',
			'gdpr_telefono' =>'Telefono',
			'gdpr_fax' =>'Fax',
			'gdpr_email' => 'email Associazione',
			'gdpr_dpo_denomination' => 'Data Protection Officer (DPO)',
			'gdpr_dpo_email' => 'DPO email',
			'gdpr_dpo_telefono' => 'DPO Telefono',

			'VapidPublic' => 'Chiave pubblica',
			'VapidSecret' => 'Chiave privata',

			'PAYPAL_CLIENT_ID' => 'Chiave pubblica',
			'PAYPAL_CLIENT_SECRET' => 'Chiave privata',

			'GoogleOauthClientId' => 'Google App ID',
			'GoogleOauthClientSecret' => 'Google App Secret',
			'facebookAppID' => 'Facebook App ID',
			'facebookAppVersion' => 'Facebook App Version',
			'telegramBotName' => 'Telegram Bot Name',
			'telegramToken' => 'Telegram Token',

			'reCaptcha2PrivateKey' => 'Google reCaptcha2 Private Key' ,
			'reCaptcha2PublicKey' => 'Google reCaptcha2 Public Key' ,

			'fileSystemStorageKey' => Yii::t('model','File System Storage Key'),
		);
	}
}
