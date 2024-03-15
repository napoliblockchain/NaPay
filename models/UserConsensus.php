<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_consensus".
 *
 * @property int $id
 * @property int $user_id
 * @property int $marketing
 * @property int $dati_personali
 * @property int $timestamp
 *
 * @property Users $user
 */
class UserConsensus extends \yii\db\ActiveRecord
{
    public $consenso_statuto;
    public $consenso_privacy;
    public $consenso_pos;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_consensus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'timestamp'], 'required'],
            [['user_id', 'marketing', 'dati_personali', 'timestamp'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'marketing' => Yii::t('app', 'Marketing'),
            'dati_personali' => Yii::t('app', 'Dati Personali'),
            'timestamp' => Yii::t('app', 'Timestamp'),
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\UserConsensusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\UserConsensusQuery(get_called_class());
    }
}
