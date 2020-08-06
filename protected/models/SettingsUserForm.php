
<?php

/**
 * This is the model class for table "np_settings".
 *
 * The followings are the available columns in table 'np_settings_user':
 * @property integer $id_user
 * @property integer $id_gateway
 * @property integer $id_wallet
 * @property integer $id_exchange
 * @property string $exchange_secret
 * @property string $exchange_key
 * @property string $only_for_bitstamp_id
 * @property string $association_percent
 * @property string $association_receiving_address
 * @property string $webapp_percent
 * @property string $only_for_bitstamp_liquidation_deposit_address
 *
 */
class SettingsUserForm extends CFormModel
{
	public $id_user;
	public $id_gateway;
	public $id_wallet;
	public $id_exchange;
	public $exchange_secret;
	public $exchange_key;
	public $withdrawal_exchange_secret;
	public $withdrawal_exchange_key;
	public $only_for_bitstamp_id;
	public $association_percent;
	public $association_receiving_address;
	public $webapp_percent;
	public $only_for_bitstamp_liquidation_deposit_address;
	public $bank_name;
	public $bank_iban;
	public $bank_bic;
	public $bank_address;
	public $bank_postal_code;
	public $bank_city;
	public $bank_country;

	// new config parameters
	public $blockchainAddress;
	public $blockchainAsset;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('id_gateway', 'required'),
			array('id_user, id_exchange, id_gateway, id_wallet', 'numerical', 'integerOnly'=>true),
			array('withdrawal_exchange_secret, withdrawal_exchange_key, exchange_secret, exchange_key, association_receiving_address,only_for_bitstamp_liquidation_deposit_address', 'length', 'max'=>250),
			array('bank_name, bank_iban, bank_bic,bank_address,bank_city, blockchainAddress', 'length', 'max'=>250),
			array('blockchainAsset', 'length', 'max'=>1000),
			array('only_for_bitstamp_id, association_percent, webapp_percent, bank_postal_code', 'length', 'max'=>10),
			array('bank_country', 'length', 'max'=>2),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_setting' => 'Id Impostazioni',
			'id_user' => 'Id Utente',
			'id_gateway' => 'Gateway',
			'id_wallet' => 'Wallet selezionato',
			'id_exchange' => 'Exchange',
			'exchange_secret' => 'Chiave Segreta: info',
			'exchange_key' => 'Chiave Pubblica: info',
			'withdrawal_exchange_secret' => 'Chiave Segreta: withdraw',
			'withdrawal_exchange_key' => 'Chiave Pubblica: withdraw',
			'only_for_bitstamp_id'=>'Bitstamp ID',
			'association_percent'=>'Percentuale Associazione di categoria',
			'association_receiving_address'=>'Indirizzo di ricezione per Associazione di categoria',
			'webapp_percent'=>'Percentuale Associazione Blockchain Napoli',
			'only_for_bitstamp_liquidation_deposit_address'=>'Indirizzo conversione automatica bitcoin',
			'bank_name'=>'Intestazione c/c',
			'bank_iban'=>'IBAN',
			'bank_bic'=>'BIC',
			'bank_address'=>'Indirizzo',
			'bank_postal_code'=>'Codice Postale',
			'bank_city'=>'Città',
			'bank_country'=>'Stato',

			'blockchainAsset' => 'Asset abilitati',
			'blockchainAddress' => 'URL del Server Blockchain',
		);
	}
}
