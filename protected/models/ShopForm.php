<?php


class ShopForm extends CFormModel
{
	public $StoreId;
	public $Title;
	public $Currency;
	public $ShowCustomAmount;
	public $ShowDiscount;
	public $EnableTips;
	public $ButtonText;
	public $CustomButtonText;
	public $CustomTipText;
	public $CustomTipPercentages;
	public $CustomCSSLink;
	public $Template;
	public $NotificationUrl;
	public $NotificationEmail;
	public $RedirectAutomatically;
	public $Description;
	public $files;
	public $EmbeddedCSS;
	public $NotificationEmailWarning;
	public $EnableShoppingCart;

	/**
 	 * Declares the validation rules.
 	 * The rules state that username and password are required,
 	 * and password needs to be authenticated.
 	 */
 	public function rules()
 	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('Title, ButtonText, CustomButtonText, CustomTipText', 'required'),
		);
 	}


 	/**
 	 * Declares attribute labels.
 	 */
 	public function attributeLabels()
 	{
 		return array(
		'StoreId' => '',
	    'Title' =>  Yii::t('model','Title'),
	    'Currency' => '*',
	    'ShowCustomAmount' => Yii::t('model','User can input custom amount'),
	    'ShowDiscount' => Yii::t('model','User can input discount in %'),
	    'EnableTips' => Yii::t('model','Enable tips'),
	    'ButtonText' => Yii::t('model','Text to display on each buttons for items with a specific price'),
	    'CustomButtonText' => Yii::t('model','Text to display on buttons next to the input allowing the user to enter a custom amount'),
	    'CustomTipText' => Yii::t('model','Text to display in the tip input'),
	    'CustomTipPercentages' => Yii::t('model','Tip percentage amounts (comma separated)'),
	    'CustomCSSLink' => '',
	    'Template' => '',
	    'NotificationUrl' => '',
	    'NotificationEmail' => '',
	    'RedirectAutomatically' => '',
	    'Description' => '',
	    'files' => '',
	    'EmbeddedCSS' => '',
	    'NotificationEmailWarning' => '',
	    'EnableShoppingCart' => '*',
 		);
 	}
}
