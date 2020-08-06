<?php

/**
 * This is the model class for table "st_quote".
 *
 * The followings are the available columns in table 'st_quote':
 * @property integer $id_quota
 * @property string $description
 * @property integer $extension
 * @property double $importo
 */
class Quote extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'st_quote';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('description, extension, importo', 'required'),
			array('extension', 'numerical', 'integerOnly'=>true),
			array('importo', 'numerical'),
			array('description', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_quota, description, extension, importo', 'safe', 'on'=>'search'),
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
			'id_quota' => 'Id Quota',
			'description' => 'Descrizione',
			'extension' => 'Scadenza',
			'importo' => 'Importo',
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

		$criteria->compare('id_quota',$this->id_quota);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('extension',$this->extension);
		$criteria->compare('importo',$this->importo);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Quote the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
