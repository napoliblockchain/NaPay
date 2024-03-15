<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\Modal;
use yii\helpers\Url;

$this->title = 'Registrazione';
// $this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(['id' => 'signup-form']) ?>

<div class="site-login mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="container mt-4">
                <div class="row align-items-center justify-content-center ">
                    <div class="col-lg-6 align-self-end">
                        <div class="card text-left">
                            <div class="card-header text-center">
                                <h4 class="login-box-msg"><?= Yii::t('app', 'Registrazione Account') ?></h4>
                            </div>
                            <div class="card-body login-card-body text-left" id="loginBody">
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

                                <?= $form->field($model, 'password', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3']
                                ])
                                    ->label(false)
                                    ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

                                <?= $form->field($model, 'repeat_password', [
                                    'options' => ['class' => 'form-group has-feedback'],
                                    'inputTemplate' => '{input}<div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>',
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'input-group mb-3']
                                ])
                                    ->label(false)
                                    ->passwordInput(['placeholder' => $model->getAttributeLabel('repeat_password')]) ?>

                                <div class="row">
                                    <div class="col-12">
                                        <?= Html::submitButton('Prosegui...', [
                                            'class' => 'btn btn-primary w-100',
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.login-card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
Modal::begin([
    'id' => 'consensus',
    'title' => '<h4>' . Yii::t('app', 'Confirm to: ') . '</h4>',
]);
?>



<!-- STATUTO -->
<div class="form-group">
    <?= $form->field($consensus, 'consenso_statuto')->checkbox() ?><i>&nbsp;Ho letto lo <a href="https://napoliblockchain.it/chi-siamo/statuto-associazione-napoli-blockchain-ets/" target="_blank">STATUTO</a> e ne condivido ogni punto.</i>
</div>

<!-- PRIVACY -->
<div class="form-group">
    <?= $form->field($consensus, 'consenso_privacy')->checkbox() ?><i>&nbsp;Ho letto l'<a href="<?= Url::to(['site/printPrivacy']); ?>" target="_blank">INFORMATIVA SULLA PRIVACY</a> e autorizzo al trattamento dei miei dati personali.</i>
</div>

<!-- TERMINI -->
<div class="input-group">
    <?= $form->field($consensus, 'dati_personali')->checkbox() ?><i>&nbsp;Accetto le <a href="<?= Url::to(['site/termOfUse']); ?>" target="_blank">Condizioni di utilizzo</a> del software.</i>
</div>

<!-- TERMINI -->
<div class="input-group">
    <?= $form->field($consensus, 'marketing')->checkbox() ?><i>&nbsp;Acconsento al trattamento dei dati per finalità di marketing.</i>
</div>

<div class="form-group">
    <?= Html::button('<i class="fas fa-save mr-1"></i>' . Yii::t('app', 'Save'), ['class' => 'btn btn-success js-confirm']) ?>
</div>


<?php
Modal::end();
?>
<?php ActiveForm::end(); ?>