<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception$exception */

use yii\helpers\Html;
use app\components\Log;

$this->context->layout = (!Yii::$app->user->isGuest) ? 'main' : 'login';

// Log message
$message_log = Yii::t('app', 'ERROR for user {user}. {title}: {message}', [
    'user' => Yii::$app->user->identity->username,
    'title' => $name,
    'message' => $message,
]);
Log::save(Yii::$app->controller->id, (Yii::$app->controller->action->id), $message_log);
// end log message

$this->title = $name;
?>
<div class="site-error">
    <div class="row">
        <div class="col-lg-12 col-12">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h1><?= Html::encode($this->title) ?></h1>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <?= nl2br(Html::encode($message)) ?>
                    </div>

                    <p>
                        L'errore sopra riportato si è verificato mentre il server Web stava elaborando la tua richiesta.
                    </p>
                    <p>
                        Per favore, contattaci se ritieni che si tratti di un errore del server. Grazie.
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>