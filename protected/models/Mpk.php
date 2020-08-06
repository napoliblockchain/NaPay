<?php

/**
 * This is the model class for table "np_mpk".
 *
 * The followings are the available columns in table 'np_mpk':
 * @property integer $id_mpk
 * @property integer $id_store
 * @property string $asset
 * @property string $AddressType
 * @property string $DerivationScheme
 * @property string $Addresses
 */
class Mpk extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_mpk';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_store, asset, AddressType, DerivationScheme', 'required'),
			array('id_store', 'numerical', 'integerOnly'=>true),
			array('asset', 'length', 'max'=>20),
			array('AddressType', 'length', 'max'=>50),
			array('DerivationScheme', 'length', 'max'=>1000),
			array('Addresses', 'length', 'max'=>1500),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_mpk, id_store, asset, AddressType, DerivationScheme, Addresses', 'safe', 'on'=>'search'),
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
			'id_mpk' => 'Id Mpk',
			'id_store' => 'Id Store',
			'asset' => 'Asset',
			'AddressType' => 'Address Type',
			'DerivationScheme' => 'Derivation Scheme',
			'Addresses' => 'Indirizzi',
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

		$criteria->compare('id_mpk',$this->id_mpk);
		$criteria->compare('id_store',$this->id_store);
		$criteria->compare('asset',$this->asset,true);
		$criteria->compare('AddressType',$this->AddressType,true);
		$criteria->compare('DerivationScheme',$this->DerivationScheme,true);
		$criteria->compare('Addresses',$this->Addresses,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Mpk the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
