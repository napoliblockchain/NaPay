<?php
namespace app\components;

use Yii;
use yii\base\Component;

class sendMail extends Component
{
    public static function toUser ($user, $htmlView, $subject)
    {
        return Yii::$app->mailer->compose($htmlView, ['user' => $user])
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
            ->setTo($user->email)
            ->setSubject(Yii::$app->name.' - ' . $subject)
            ->send();
    }

    public static function toAdmins ($user, $htmlView, $subject)
    {
        $models = User::getAdmins();

        foreach ($models as $model) {
            $adminMails[] = $model->email;
        }
        // echo '<pre>' . print_r($adminMails, true) . '</pre>';exit;

        return Yii::$app->mailer->compose($htmlView, ['user' => $user])
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
            ->setTo($adminMails)  // invia mail a tutti gli admin
            ->setSubject(Yii::$app->name . ' - ' . $subject)
            ->send();
    }

}
