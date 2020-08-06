<?php
/* @var $this UsersController */
/* @var $dataProvider CActiveDataProvider */

$url = Yii::app()->createUrl('users/index');
$printURL = Yii::app()->createUrl('users/print',['typelist'=>0]);
$exportURL = Yii::app()->createUrl('users/export',['typelist'=>0]);


$usersTypeList = CHtml::listData(UsersType::model()->findAll(), 'id_users_type', 'desc');

$myList = <<<JS
    lista = {
		cambia: function(val){
            var url = '{$url}' + "&typelist="+val;
            var print = '{$printURL}' + "&typelist="+val;
            var exp = '{$exportURL}' + "&typelist="+val;

            $('#print-button').attr("href", print);
            $('#export-button').attr("href", exp);

            lista.btnClass(val);
            // AGGIORNA yiiGridView in ajax
            $.fn.yiiGridView.update('users-grid', {
               type: 'GET',
               url: url,
               success: function() {
                   $('#users-grid').yiiGridView('update',{
                       url: url
                   });
               }
            });
		},
        btnClass: function(val){
            $('.btn').removeClass("active");
            $('.btn-'+val).addClass("active");

        }
	}


JS;
Yii::app()->clientScript->registerScript('myList', $myList);

$activeButton = [
    0 => '',    // tutti
    1 => '',    // attivi
    2 => '',    // in scadenza
    3 => ''     // scaduti
];

$activeButton[1] = 'active';
if (isset($_GET['typelist']))
    $activeButton[$_GET['typelist']] = 'active';

?>
<style>
/* table.items th {
    background-color: #f0f0f0;
} */
</style>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
                <div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-users"></i>
						<span class="card-title">Lista Soci</span>
                        <div class="float-right">
							<?php $actionURL = Yii::app()->createUrl('users/create'); ?>
							<a href="<?php echo $actionURL;?>">
								<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus"></i></button>
							</a>
						</div>

					</div>
					<div class="card-body">
                        <span>
                            <button type='button' class='btn btn-outline-primary btn-sm <?php echo $activeButton[1]; ?>' onclick='lista.cambia(1);'>Attivi</button>
                            <button type='button' class='btn btn-outline-warning btn-sm <?php echo $activeButton[2]; ?>' onclick='lista.cambia(2);'>In scadenza</button>
                            <button type='button' class='btn btn-outline-danger btn-sm <?php echo $activeButton[3]; ?>' onclick='lista.cambia(3);'>Scaduti</button>
                            <button type='button' class='btn btn-outline-info btn-sm <?php echo $activeButton[0]; ?>' onclick='lista.cambia(0);'>Tutti</button>
                        </span>
        				<div class="table-responsive table--no-card m-t-40">
        					<?php
        					$this->widget('zii.widgets.grid.CGridView', array(
                                'id'=>'soci-grid',
        						//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
        						//'htmlOptions' => array('class' => 'table table-borderless table-data2 table-earning '),
                                'htmlOptions' => array('class' => 'table table-wallet '),
        					    'dataProvider'=>$modelc->search(),
                                'id'=>'users-grid',
                                'filter'=>$modelc,
                                'enablePagination'  => true,
                                // 'enableSorting' => true,
                                // 'pager' => array(
                                //     'class'                 => 'CLinkPager',
                                //     'prevPageLabel'         => 'Precedente',
                                //     'nextPageLabel'         => 'Successiva',
                                //     'header'                => 'Vai alla pagina: ',
                                //     'previousPageCssClass'  => 'btn btn-info-outline btn-sm',
                                //     'selectedPageCssClass'  => 'btn btn-warning-outline btn-sm',
                                //     'internalPageCssClass'  => 'btn btn-info-outline btn-sm',
                                //     'firstPageCssClass'     => 'btn btn-info-outline btn-sm',
                                //     'nextPageCssClass'      => 'btn btn-info-outline btn-sm',
                                // ),
        						'columns' => array(
                                    // 'id_user',
        							array(
        								'type'=> 'raw',
        					            'name'=>'name',
                                        'header'=>'Nome',
        								'value' => 'CHtml::link(CHtml::encode(strtoupper(Users::model()->findByPk($data->id_user)->surname).chr(32).Users::model()->findByPk($data->id_user)->name), Yii::app()->createUrl("users/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_user)))',
                                        'filter' => CHtml::listData(Users::model()->findAll(array('order'=>'surname ASC, name ASC')), 'surname', function($items) {
                                        	 return $items->surname.' '.$items->name;
                                        })
        					        ),
        							array(
        					            'name'=>'email',
        								'type'=>'raw',
        								'value' => 'CHtml::link(CHtml::encode($data->email), Yii::app()->createUrl("users/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_user)))',
        					        ),
        							array(
        					            'name'=>'id_carica',
                                        'header'=>'Carica',
        								'value'=>'Cariche::model()->findByPk($data->id_carica)->description',
                                        'filter'=>CHtml::listData(Cariche::model()->findAll(), 'id_carica', 'description'),
        					        ),
                                    array(
        					            'name'=>'corporate',
                                        'header'=>'P/F',
        								'value'=>'($data->corporate == 0) ? "No" : "Si"',
                                        'filter'=>array('0'=>'No','1'=>'Si'),
        					        ),
        							array(
        					            'name'=>'id_users_type',
                                        'header'=>'Tipo',
        								'value'=>'UsersType::model()->findByPk($data->id_users_type)->desc',
                                        'filter'=>$usersTypeList,
        					        ),
        							array(
        								'type'=> 'raw',
        					            'name'=>'status_activation_code',
                                        'header'=>'Iscrizione',
        								'value'=>'WebApp::StatoPagamenti($data->id_user)',
                                        'filter'=>""
        					        ),
                                    [
                                        'value' =>''
                                        ]
                                    // array(
							      	// 	'name'=>'',
									// 	'value' => '',
							    	// ),

        						)
        					));
        					?>
        				</div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                			<div class="col-md-12">
                				<div class="overview-wrap">
                					<h2 class="title-1">
                						<form>
                                            <a href="<?php echo $printURL;?>" target="_blank" id='print-button'>
                    							<button type="button" class="btn btn-secondary">Stampa</button>
                    						</a>
                    						<a href="<?php echo $exportURL;?>" target="_blank" id='export-button'>
                    							<button type="button" class="btn btn-warning">Esporta</button>
                    						</a>
                						</form>

                					</h2>
                				</div>
                			</div>
                		</div>
                    </div>
                </div>
			</div>
		</div>

		<?php echo Logo::footer(); ?>
	</div>
</div>
