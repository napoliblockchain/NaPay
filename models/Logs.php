<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property int $id
 * @property int $timestamp
 * @property int $user_id
 * @property string $remote_address
 * @property string $browser
 * @property string $controller
 * @property string $action
 * @property string $description
 */
class Logs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['timestamp', 'user_id', 'remote_address', 'browser', 'controller', 'action', 'description'], 'required'],
            [['timestamp', 'user_id'], 'integer'],
            [['description'], 'string'],
            [['remote_address'], 'string', 'max' => 20],
            [['browser'], 'string', 'max' => 256],
            [['controller', 'action'], 'string', 'max' => 60],
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
            'timestamp' => Yii::t('app', 'Timestamp'),
            'user_id' => Yii::t('app', 'User ID'),
            'remote_address' => Yii::t('app', 'Remote Address'),
            'browser' => Yii::t('app', 'Browser'),
            'controller' => Yii::t('app', 'Controller'),
            'action' => Yii::t('app', 'Action'),
            'description' => Yii::t('app', 'Description'),
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
     * @return \app\models\query\LogsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\LogsQuery(get_called_class());
    }
}
