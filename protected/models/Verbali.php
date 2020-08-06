<?php

/**
 * This is the model class for table "st_verbali".
 *
 * The followings are the available columns in table 'st_verbali':
 * @property integer $id_verbali
 * @property string $data_verbale
 * @property string $url_verbale
 * @property string $descrizione_verbale
 */

class Verbali extends CActiveRecord
{
	private $tempfile;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'st_verbali';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('data_verbale, url_verbale, descrizione_verbale', 'required'),

			array('data_verbale', 'type', 'type' => 'date', 'message' => '{attribute}: non è nel formato corretto! (gg/mm/aaaa)', 'dateFormat' => 'dd/MM/yyyy'),

			array('url_verbale', 'length', 'max'=>250),
			array('descrizione_verbale', 'length', 'max'=>500),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_verbali, data_verbale, url_verbale, descrizione_verbale', 'safe', 'on'=>'search'),
		);
	}

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
			'id_verbali' => '#',
			'data_verbale' => 'Data Assemblea',
			'url_verbale' => 'File',
			'descrizione_verbale' => 'Descrizione Verbale',
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

		$criteria->compare('id_verbali',$this->id_verbali);
		$criteria->compare('data_verbale',$this->data_verbale,true);
		$criteria->compare('url_verbale',$this->url_verbale,true);
		$criteria->compare('descrizione_verbale',$this->descrizione_verbale,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Verbali the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
