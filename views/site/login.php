<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login mt-4">
    <div class="row">
        <div class="col-lg-12">

            <div class="container mt-4">
                <div class="row align-items-center justify-content-center ">
                    <div class="col-lg-6 align-self-end">
                        <div class="card text-left">
                            <div class="card-header text-center">
                                <h4 class="login-box-msg"><?= Yii::t('app', 'Accedi per iniziare la sessione') ?></h4>
                            </div>
                            <div class="card-body login-card-body text-left" id="loginBody">

                                <?php $form = ActiveForm::begin(['id' => 'login-form']) ?>

                                <?= $form->field($model, 'username', [
                                    'options' => ['class' => 'form-group has-feedback shadow p-2'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>',
                                    'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group input-group-sm mb-1'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label($model->getAttributeLabel('username'))
                                    ->textInput(['placeholder' => 'Inserisci il tuo nome utente']) ?>

                                <?= $form->field($model, 'password', [
                                    'options' => ['class' => 'form-group has-feedback shadow p-2'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>',
                                    'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group input-group-sm mb-1'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label($model->getAttributeLabel('password'))
                                    ->passwordInput(['placeholder' => 'Inserisci la password']) ?>

                                <?= $form->field($model, 'otpCode', [
                                    'options' => ['class' => 'form-group has-feedback shadow p-2'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-key"></span></div></div>',
                                    'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group input-group-sm mb-1'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label($model->getAttributeLabel('otpCode'))
                                    ->textInput(['placeholder' => 'Inserisci il codice One Time Password']) ?>

                                <div class="row">
                                    <div class="col-12">
                                        <?= Html::submitButton('Accedi', [
                                            'class' => 'btn btn-primary w-100',
                                        ]) ?>
                                    </div>
                                </div>
                                <?php ActiveForm::end(); ?>

                            </div>
                            <!-- /.login-card-body -->
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>