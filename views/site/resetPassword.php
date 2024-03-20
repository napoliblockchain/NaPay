<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = Yii::t('app', 'Request password reset');
?>

<div class="site-login mt-4">
    <div class="row">
        <div class="col-lg-12">

            <div class="container mt-4">
                <div class="row align-items-center justify-content-center ">
                    <div class="col-lg-6 align-self-end">
                        <div class="card card-secondary text-left">
                            <div class="card-header text-center">
                                <h4><?= Html::encode($this->title) ?></h4>
                            </div>
                            <div class="card-body login-card-body text-left" id="loginBody">


                                <?php $form = ActiveForm::begin(['id' => 'login-form']) ?>

                                <?= $form->field($model, 'password', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label(false)
                                    ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

                                <?= $form->field($model, 'repeat_password', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3'],
                                    'inputOptions' => ['autocomplete' => 'off'],
                                ])
                                    ->label(false)
                                    ->passwordInput(['placeholder' => $model->getAttributeLabel('repeat_password')]) ?>



                                <div class="row">
                                    <div class="col-12">
                                        <?= Html::submitButton('<i class="fa fa-sign-in-alt"></i> ' . Yii::t('app', 'Submit'), [
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