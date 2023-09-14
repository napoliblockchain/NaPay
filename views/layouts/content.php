<?php
/* @var $content string */

use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Url;
?>
<div class="content-wrapper bg-transparent">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">

                    <!-- <h1 class="m-0">
                        <?php
                        // if (!is_null($this->title) && $this->context->id != 'invoices') {
                        //     echo \yii\helpers\Html::encode($this->title);
                        // } else {
                        //     echo \yii\helpers\Inflector::camelize($this->context->id);
                        // }
                        ?>
                    </h1> -->
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <?php
                    echo Breadcrumbs::widget([
                        'homeLink' => [
                            'label' => 'Home',
                            'url' => Url::to(['site/index'], true),
                            'class' => 'breadcrumb', // Opzionale: Aggiungi una classe CSS personalizzata
                        ],
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        'options' => [
                            'class' => 'breadcrumb float-sm-right'
                        ]
                    ]);
                    ?>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <?= $content ?>
        <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>