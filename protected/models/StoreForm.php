<?php


class StoreForm extends CFormModel
{
    // new_store
	public $store_denomination;

    // general_settings
    public $bps_storeid;
    public $store_website;
	public $network_fee_mode;
	public $invoice_expiration;
	public $monitoring_expiration;
	public $payment_tolerance;
	public $speed_policy;

    // rates
    public $preferred_exchange;
    public $spread;
    public $default_currency_pairs;

    // checkout
    public $CustomLogo;
    public $DefaultPaymentMethod;
    public $DefaultLang;
    public $RequiresRefundEmail;
    public $CustomCSS;
    public $HtmlTitle;
    public $OnChainMinValue;
    public $LightningMaxValue;
    public $LightningAmountInSatoshi;
    public $RedirectAutomatically;
	public $ShowRecommendedFee; // aggiunto con la versione 1.0.3.137
	public $RecommendedFeeBlockTarget; // aggiunto con la versione 1.0.3.137
	public $command; // aggiunto con la versione 1.0.3.137

    // save_mpk
    public $AddressType;
    public $DerivationScheme;


	// atm ?
	public $readExchangeId; //$idExchange;
	public $readKeyPublic; //$apiReadPublic;
	public $readKeySecret; //$apiReadPrivate;
	public $readBitstampId;
	public $writeExchangeId; //$idExchange;
	public $writeKeyPublic; //$apiWritePublic;
	public $writeKeySecret; //$apiWritePrivate;
	public $writeBitstampId;
	public $coinsEnabled;
	public $userFeeMode;


	// test deposit
	public $coin;
	public $amount;
	public $chargedPrice;
	public $address;
	public $addressTag;
	public $securityToken;
	public $nonce;


	/**
 	 * Declares the validation rules.
 	 * The rules state that username and password are required,
 	 * and password needs to be authenticated.
 	 */
 	public function rules()
 	{
 		return array(
			array('readKeySecret, writeKeySecret', 'encrypt'),
 		);
 	}

	public function encrypt($attribute,$params)
	{
		$this->$attribute = crypt::Encrypt($this->$attribute);
	}

 	/**
 	 * Declares attribute labels.
 	 */
 	public function attributeLabels()
 	{
 		return array(
 			'merchant_email'=>'email Commerciante',
 			'merchant_password'=>'password Commerciante',
			'store_denomination'=>'Denominazione negozio',

            'bps_storeid'=>'ID Store',
			'store_website'=>'sito web negozio',
			'network_fee_mode'=>'Aggiungi le network fee all\'invoice',
			'invoice_expiration'=>'scadenza invoice in minuti',
			'monitoring_expiration'=>'monitoraggio dell\'invoice in minuti (60*12 = 12 ore)',
			'payment_tolerance'=>'considera l\'invoice pagata anche se inferiore di x%',
			'speed_policy'=>'conferme in blocchi (0,1,2,6)',

            'preferred_exchange'=>'Exchange Preferito',
            'spread'=>'aggiungi spread al tasso dell\'exchange di ... %',
            'default_currency_pairs'=>'Coppia di valuta per richiedere il Tasso via REST (formato BTC_USD, BTC_EUR)',

            'CustomLogo' => 'URL Immagine per il logo dell\'invoice',
			'CustomCSS' => 'URL per customizzare l\'invoice',
            'HtmlTitle' => 'Titolo dell\'invoice',
			'DefaultPaymentMethod' => 'Metodo di pagamento di default (BTC, LTC, ecc...)',
            'DefaultLang' => 'Lingua di default (it-IT, us-US, ecc...)',
            'RequiresRefundEmail' => 'Chiedi la mail dell\'utente per il Refund (true/false)',
            'OnChainMinValue' => 'Non proporre un pagamento LN se il valore dell\'invoice è inferiore a ... (5.50 USD) ',
            'LightningMaxValue' => 'Non proporre un pagamento LN se il valore dell\'invoice è superiore a ... (5.50 USD) ',
            'LightningAmountInSatoshi' => 'Mostra i pagamenti LN in satoshi',
            'RedirectAutomatically' => 'Reindirizza automaticamente al "redirect URL" dopo un pagamento',
			'ShowRecommendedFee' => 'Mostra le fee raccomandate (Saranno mostrate solo per i pagamenti onchain)',
			'RecommendedFeeBlockTarget' => 'N. di blocchi da considerare per le fee raccomandate',

			// mpk
			'AddressType' => 'Tipo di indirizzi',
            'DerivationScheme' => 'Master Public Key',

			// atm
			'readExchangeId' => 'Seleziona l\'exchange in sola lettura',
			'readKeyPublic' => 'Chiave pubblica',
			'readKeySecret' => 'Chiave segreta',
			'readBitstampId' => 'ID (solo per Bitstamp)',

			'writeExchangeId' => 'Seleziona l\'exchange in scrittura',
			'writeKeyPublic' => 'Chiave pubblica',
			'writeKeySecret' => 'Chiave segreta',
			'writeBitstampId' => 'ID (solo per Bitstamp)',

			'coinsEnabled' => 'Asset abilitati per l\'acquisto',
			'userFeeMode' => 'Percentuale Fee di deposito a carico del Commerciante',

			// form test deposito
			'coin' => 'crypto da richiedere',
			'amount' => 'importo da richiedere',
			'chargedPrice' => 'tasso applicato al cliente',
			'address' => 'indirizzo del cliente',
			'addressTag' => 'tag richiesto da alcuni asset per il trasferimento',
			'securityToken' => 'token per autenticità della transazione',
			'nonce' => 'nonce',
 		);
 	}
}
