<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\components\User;
use app\components\Crypt;

?>
<aside class="main-sidebar  elevation-4 bg-transparent">
    <!-- Brand Logo -->
    <a href="<?= Url::to(['site/index'], true) ?>" class="brand-link bg-light ">
        <?=
        Html::img('@web/bundles/site/images/logo.png', [
            'alt' => 'logo',
            'class' => "brand-image elevation-3",
            // 'style' => 'opacity: .8; height: 50px; width: 50px; top: 2.5px; position: absolute;'
        ]) ?>
        <span class="brand-text font-weight-light"><?= Yii::$app->name ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar sidebar-dark-primary"> <!--  bg-gradient-light --> 
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?=
                Html::img(Yii::$app->user->identity->picture, [
                    'alt' => 'user image',
                    'class' => "img-circle elevation-2",
                ]) ?>

            </div>
            <div class="info">
                <a href="<?= Url::to(['users/view', 'id' => Crypt::encrypt(Yii::$app->user->id)]) ?>" class="d-block">
                    <?php if (!Yii::$app->user->isGuest) : ?>
                        <?= Yii::$app->user->identity->first_name . chr(32) . Yii::$app->user->identity->last_name ?>
                    <?php endif ?>
                </a>
            </div>
        </div>

        <?php


        $items = [
            ['label' => Yii::t('app', 'Dashboard'), 'url' => ['site/index'], 'iconStyle' => 'fas', 'icon' => 'tachometer-alt'],
            ['label' => Yii::t('app', 'Esercenti'), 'url' => ['merchants/index'], 'iconStyle' => 'fas', 'icon' => 'store', 'visible' => User::isAdministrator()], //administrator
            ['label' => Yii::t('app', 'Negozi'), 'url' => ['stores/index'], 'iconStyle' => 'fas', 'icon' => 'shopping-cart', 'visible' => User::can(30)],
            ['label' => Yii::t('app', 'Pos'), 'url' => ['pos/index'], 'iconStyle' => 'fas', 'icon' => 'mobile-alt'],
            ['label' => Yii::t('app', 'Transazioni'), 'url' => ['invoices/index'], 'iconStyle' => 'fas', 'icon' => 'file-invoice'],
            ['label' => Yii::t('app', 'Pannello Admin'), 'url' => ['admin/index'], 'iconStyle' => 'fas', 'icon' => 'cog', 'visible' => User::can(40)],
                      
            ['label' => 'Developer', 'header' => true, 'visible' => User::isAdministrator()],
            ['label' => 'Debug', 'icon' => 'bug', 'url' => ['/debug'], 'target' => '_blank', 'visible' => User::isAdministrator()],
            ['label' => 'Gii',  'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank', 'visible' => User::isAdministrator()],
            ['label' => 'PHPInfo', 'iconStyle' => 'fab', 'icon' => 'php', 'url' => ['/site/phpinfo'], 'target' => '_blank', 'visible' => User::isAdministrator()],
        ];


        ?>
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => $items,
                // [
                //     [
                //         'label' => Yii::t('app','Manage Payments'),
                //         'icon' => 'tasks',
                //         // 'badge' => '<span class="right badge badge-info">2</span>',
                //         'items' => [
                //             ['label' => Yii::t('app','Invoices'), 'url' => ['invoices/index'], 'iconStyle' => 'far', 'icon' => 'star'],
                //             ['label' => Yii::t('app','Notifications'), 'url' => ['notifications/index'], 'iconStyle' => 'far', 'icon' => 'comment'],
                //         ]
                //     ],
                //     ['label' => 'Simple Link', 'icon' => 'th', 'badge' => '<span class="right badge badge-danger">New</span>'],
                //     ['label' => 'Yii2 PROVIDED', 'header' => true],
                //     ['label' => 'Login', 'url' => ['site/login'], 'icon' => 'sign-in-alt', 'visible' => Yii::$app->user->isGuest],
                //     ['label' => 'Gii',  'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank'],
                //     ['label' => 'Debug', 'icon' => 'bug', 'url' => ['/debug'], 'target' => '_blank'],
                //     ['label' => 'MULTI LEVEL EXAMPLE', 'header' => true],
                //     ['label' => 'Level1'],
                //     [
                //         'label' => 'Level1',
                //         'items' => [
                //             ['label' => 'Level2', 'iconStyle' => 'far'],
                //             [
                //                 'label' => 'Level2',
                //                 'iconStyle' => 'far',
                //                 'items' => [
                //                     ['label' => 'Level3', 'iconStyle' => 'far', 'icon' => 'dot-circle'],
                //                     ['label' => 'Level3', 'iconStyle' => 'far', 'icon' => 'dot-circle'],
                //                     ['label' => 'Level3', 'iconStyle' => 'far', 'icon' => 'dot-circle']
                //                 ]
                //             ],
                //             ['label' => 'Level2', 'iconStyle' => 'far']
                //         ]
                //     ],
                //     ['label' => 'Level1'],
                //     ['label' => 'LABELS', 'header' => true],
                //     ['label' => 'Important', 'iconStyle' => 'far', 'iconClassAdded' => 'text-danger'],
                //     ['label' => 'Warning', 'iconClass' => 'nav-icon far fa-circle text-warning'],
                //     ['label' => 'Informational', 'iconStyle' => 'far', 'iconClassAdded' => 'text-info'],
                // ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>