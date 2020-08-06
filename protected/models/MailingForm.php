<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class MailingForm extends CFormModel
{
	public $subject;
	public $data;
	public $time;
	public $place;
	public $body;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
			return array(
				// name, email, subject and body are required
				array('subject, data, time, place, body', 'required'),
				// email has to be a valid email address
			);

	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'subject' => Yii::t('model','Subject'),
			'data' => Yii::t('model','Date'),
			'time' => Yii::t('model','Time'),
			'place'=> Yii::t('model','Place'),
			'body' => Yii::t('model','Body'),
		);
	}
}
