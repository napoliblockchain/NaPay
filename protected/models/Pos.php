<?php

/**
 * This is the model class for table "np_pos".
 *
 * The followings are the available columns in table 'np_pos':
 * @property integer $id_pos
 * @property string $alias
 * @property integer $id_store
 * @property integer $id_maker

 */
class Pos extends CActiveRecord
{
	public function init() { $this->setTableAlias( '_pos_' ); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'np_pos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('denomination, id_store, id_merchant', 'required'),
			array('id_store, id_merchant', 'numerical', 'integerOnly'=>true),
			array('denomination', 'length', 'max'=>300),
			array('pairingCode', 'length', 'max'=>10),
			//array('date_modify, date_end', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_pos, denomination, id_store, id_merchant, pairingCode, deleted', 'safe', 'on'=>'search'),

			//regole per inserire un nome POS univoco per singolo store...
			array('denomination', 'UniquePOSDenominationMethod', 'on'=>'insert'), //SOLO NELLO SCENARIO INSERT
		);
	}
	public function UniquePOSDenominationMethod($attribute,$params)
	{
		$search = Pos::model()->findByAttributes(array(
			'id_store'=>$this->id_store,
			'denomination'=>$this->denomination,
			'deleted'=>0
		));

		if ($search !== null)
			$this->addError('denomination', "La denominazione inserita è già presente in archivio.");
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(

	   		//'posRelation' => array(self::HAS_MANY, 'Stores','id_store'),
			//'storeRelation' => array(self::HAS_MANY, 'Merchants', 'id_merchant')

		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_pos' => 'Descrizione',
			'denomination' => 'Descrizione',
			'id_store' => 'Negozio',
			'id_merchant' => 'Commerciante',
			'pairingCode'=> 'Pairing Code',
			'deleted' =>'deleted',
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

		$criteria->compare('id_pos',$this->id_pos);
		$criteria->compare('denomination',$this->denomination,true);
		$criteria->compare('id_store',$this->id_store);
		$criteria->compare('id_merchant',$this->id_merchant);
		$criteria->compare('pairingCode',$this->pairingCode);
		$criteria->compare('deleted',$this->deleted);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pos the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
