<?php

$sendmail = Yii::app()->createUrl('mailing/send');
$ajaxValidation = Yii::app()->createUrl('mailing/ajaxValidation');

$mail = <<<JS

var UsersList = JSON.parse('{$list}');
// console.log(UsersList,UsersList.length);
var confirmButton = document.querySelector('#btn-confirm');
var post;
var ajax_loader_url = 'css/images/loading.gif';

confirmButton.addEventListener('click', function(){
    event.preventDefault();

    post = {
        'subject': $('#MailingForm_subject').val(),
        'data': $('#MailingForm_data').val(),
        'time': $('#MailingForm_time').val(),
        'place': $('#MailingForm_place').val(),
        'body': $('#MailingForm_body').val()
    }

    $.ajax({
        url:'{$ajaxValidation}',
        type: "POST",
        data: post,
        dataType: "json",
        beforeSend:function(){
            $('#MailingForm_subject'+'_em_').hide().text('');
            $('#MailingForm_data'+'_em_').hide().text('');
            $('#MailingForm_time'+'_em_').hide().text('');
            $('#MailingForm_place'+'_em_').hide().text('');
            $('#MailingForm_body'+'_em_').hide().text('');
            $('#btn-confirm').hide();
            $('#btn-confirm').prop('disabled',true);
            $('#btn-confirm').after('<div class="__loading"><center><img width=20 src="'+ajax_loader_url+'" alt="loading..."></center></div>');
        },
        success:function(data){
            if (data.success===true){
                $('#email-response').show();
                var count = 0;
                for(var i = 0; i < UsersList.length; ++i){
                    //console.log('[invio mail]',UsersList[i],post);

                    $.ajax({
                        url:'{$sendmail}&id='+UsersList[i],
                        type: "POST",
                        data: post,
                        dataType: "json",
                        beforeSend:function(){
                            //$('#email-response').text('Invio mail n. '+eval(i+1)+ ' a '+UsersList[i]+'... ');
                            $('#email-response').text('Invio mail n. '+eval(i+1));
                        },
                        success:function(data){
                            $( "#email-response" ).after( '<div class="__loading"><p>'+data.text+'</p></div>' );
                        },
                        error: function(j){
                            console.log(j);
                        },
                        complete: function(data) {
                            $('#btn-confirm').show();
                            $('.__loading').remove();
                        }
                    });
                }
            }else{
                $('#btn-confirm').show();
                $('.__loading').remove();
                $('#btn-confirm').prop('disabled',false);
                for(var key in data.result) {
                    $('#'+key+'_em_').show().text(data.result[key][0]);
                }
            }
        },
        error: function(j){
            console.log(j);
        }
    });





  });

JS;
Yii::app()->clientScript->registerScript('mail', $mail, CClientScript::POS_END);
?>
