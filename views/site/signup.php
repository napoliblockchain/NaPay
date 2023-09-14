<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Registrazione';
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
                                <h4 class="login-box-msg"><?= Yii::t('app', 'Registrazione Account Amministratore') ?></h4>
                            </div>
                            <div class="card-body login-card-body text-left" id="loginBody">

                                <?php $form = ActiveForm::begin(['id' => 'signup-form']) ?>

                                <?= $form->field($model, 'username', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label(false)
                                    ->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

                                <?= $form->field($model, 'first_name', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label(false)
                                    ->textInput(['placeholder' => $model->getAttributeLabel('first_name')]) ?>

                                <?= $form->field($model, 'last_name', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label(false)
                                    ->textInput(['placeholder' => $model->getAttributeLabel('last_name')]) ?>

                                <?= $form->field($model, 'email', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label(false)
                                    ->textInput(['placeholder' => $model->getAttributeLabel('email')]) ?>


                               

                                <div class="row">
                                    <div class="col-12">
                                        <?= Html::submitButton('Registrati', [
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