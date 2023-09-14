<?php
namespace app\components;

use Yii;
use yii\base\Component;
use app\models\Logs;


class Log extends Component
{
    /**
     * Salva il log dell'applicazione
     * @param string $controller Il Controller
     * @param string $action Azione del controller
     * @param string $description Descrizione operazione
    */
    public static function save($controller, $action, $description)
    {
        $timestamp = time();
        $remoteAddress = self::get_client_ip_server();
        $browser = 'localhost';

        if (isset($_SERVER['HTTP_USER_AGENT']))
            $browser = $_SERVER['HTTP_USER_AGENT'];

        if ((isset(Yii::$app->user)) && !Yii::$app->user->isGuest){
            $id_user = Yii::$app->user->id;    
        } else {
            $id_user = 1;
        }

        $model = new Logs;
        $model->timestamp = $timestamp;
        $model->user_id = $id_user;
        $model->remote_address = $remoteAddress;
        $model->browser = $browser;
        $model->controller = $controller;
        $model->action = $action;
        $model->description = $description;

        return $model->save();
        // return (object) $model->attributes;
    }

    // Function to get the client ip address
    private static function get_client_ip_server() {
        $ipaddress = '';
        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(array_key_exists('HTTP_X_FORWARDED', $_SERVER))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(array_key_exists('HTTP_FORWARDED_FOR', $_SERVER))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(array_key_exists('HTTP_FORWARDED', $_SERVER))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(array_key_exists('REMOTE_ADDR', $_SERVER))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

}
