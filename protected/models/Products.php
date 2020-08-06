<?php

/**
 * This is the model class for table "np_products".
 *
 * The followings are the available columns in table 'np_products':
 * @property integer $id_product
 * @property integer $id_merchant
 * @property integer $id_store
 * @property integer $id_shop
 * @property string $title
 * @property string $filename
 * @property string $description
 * @property double $price
 */
class Products extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_products';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_merchant, id_store, id_shop, title, filename, description, price', 'required'),
			array('id_merchant, id_store, id_shop', 'numerical', 'integerOnly'=>true),
			array('price', 'numerical'),
			array('title', 'length', 'max'=>60),
			array('filename', 'length', 'max'=>500),
			array('description', 'length', 'max'=>500),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_product, id_merchant, id_store, id_shop, title, filename, description, price', 'safe', 'on'=>'search'),
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
			'id_product' => 'Id Product',
			'id_merchant' => 'Id Merchant',
			'id_store' => 'Negozio',
			'id_shop' => 'Self POS',
			'title' => 'Titolo',
			'filename' => 'Immagine',
			'description' => 'Descrizione',
			'price' => 'Prezzo',
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

		$criteria->compare('id_product',$this->id_product);
		$criteria->compare('id_merchant',$this->id_merchant);
		$criteria->compare('id_store',$this->id_store);
		$criteria->compare('id_shop',$this->id_shop);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('filename',$this->filename,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('price',$this->price);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Products the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
