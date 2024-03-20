<?php
namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Users;
use app\components\sendMail;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            // ['email', 'exist',
            //     'targetClass' => '\app\models\Users',
            //     'filter' => ['is_active' => Users::STATUS_ACTIVE],
            //     'message' => Yii::t('app','There is no user with this email address.')
            // ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'Email'),
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = Users::findOne([
            'is_active' => Users::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        // echo "<pre>" . print_r($user, true) . "</pre>";exit;

        if (!$user) {
            return false;
        }

        if (!Users::isPasswordResetTokenValid($user->activationCode)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        // invio la mail all'utente
        sendMail::toUser($user, 'resetPasswordUser', Yii::t('app', 'Password reset'));

        // invio solo return in quanto all'utente/hacker non è dato sapere se l'invio è stato effettuato o meno
        return;

    }
}
