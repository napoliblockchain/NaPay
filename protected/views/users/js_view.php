<?php
$resetPwd = <<<JS
	var ajax_loader_url = 'css/images/loading.gif';
	const consensoMarketing = document.querySelector('#consenso-button');

	consensoMarketing.addEventListener('click', function() {
		$.ajax({
			url:'{$modifyConsensus}',
			type: "POST",
		 	data: {
				id: '{$idUserCrypted}',
				type: 'marketing',
			},
			beforeSend: function() {
				$('#consenso-button').hide();
				$('#consenso-button').after('<div class="__loading"><center><img width=15 src="'+ajax_loader_url+'"></center></div>');
			},
			dataType: "json",
			success:function(data){
				$('.__loading').remove();
				$('#marketing_text').html(data.text);

				if (data.error){
                    return false;
                }else{
                    window.location.href = window.location.href;
                }
			},
			error: function(j){
				//something happened!!!

			}
		});

	});


	$("button[id='resetpwd-button']").click(function(){
		$.ajax({
			url:'{$resetPwdURL}',
			type: "POST",
		 	data: {id: '{$idUserCrypted}'},
			beforeSend: function() {
				$('.resetpwd__content').hide();
				$('.resetpwd__content').after('<div class="bitpay-pairing__loading"><center><img width=15 src="'+ajax_loader_url+'"></center></div>');
			},
			dataType: "json",
			success:function(data){
				$('.bitpay-pairing__loading').remove();
			$('.btn-danger').remove();
				$('.div_resetpwd__text').show();
			$('.resetpwd__text').html(data.txt);
			$('.resetpwd__content').show();
			},
			error: function(j){
				//something happened!!!
				$('.btn-danger').remove();
				$('.div_resetpwd__text').show();
				$('.resetpwd__content').show();
				$('.resetpwd__text').html(j);
			}
		});
	});

	$("button[id='sollecito-button']").click(function(){
		$.ajax({
   		 url:'{$sollecitoURL}',
		 type: "POST",
		 data: {id: '{$idUserCrypted}'},
   		 beforeSend: function() {
   			 $('#sollecito-button').html('<div class="bitpay-pairing__loading"><center><img width=15 src="'+ajax_loader_url+'"></center></div>');
   		 },
   		 dataType: "json",
   		 success:function(data){
			 $('#sollecito-button').text('Conferma');
			 $('.div_sollecito__text').show();
			 $('.sollecito__text').html(data.txt);
   		 },
   		 error: function(j){
   			 //something happened!!!
			 $('.div_sollecito__text').show();
			$('.sollecito__text').html(j);
   		 }
   	 });
	});

	function urlBase64ToUint8Array(base64String) {
	  var padding = '='.repeat((4 - base64String.length % 4) % 4);
	  var base64 = (base64String + padding)
	    .replace(/\-/g, '+')
	    .replace(/_/g, '/');

	  var rawData = window.atob(base64);
	  var outputArray = new Uint8Array(rawData.length);

	  for (var i = 0; i < rawData.length; ++i) {
	    outputArray[i] = rawData.charCodeAt(i);
	  }
	  return outputArray;
	}

	/*
     * This code checks if service workers and push messaging is supported by the current browser and if it is, it registers our sw.js file.
     */
    const applicationServerPublicKey = '{$vapidPublicKey}';
    const pushButton = document.querySelector('.js-push-btn');
    const pushButtonModal = document.querySelector('.js-push-btn-modal');

    let isSubscribed = false;
    let swRegistration = null;

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        console.log('Push is supported');
        navigator.serviceWorker.register('sw.js')
            .then(function(swReg) {
                console.log('Service Worker is registered again');

                swRegistration = swReg;
                initializeUI();
            })
            .catch(function(error) {
                console.error('Service Worker Error', error);
            });
    } else {
        console.warn('Push messaging is not supported');
        pushButtonModal.textContent = 'Push Not Supported';
    }

    /*
     * check if the user is currently subscribed
     */
    function initializeUI() {
        pushButton.addEventListener('click', function() {
            pushButtonModal.disabled = true;
            if (isSubscribed) {
                unsubscribeUser();
            } else {
                subscribeUser();
            }
        });
        // Set the initial subscription value
        swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                isSubscribed = !(subscription === null);

                updateSubscriptionOnServer(subscription);

            if (isSubscribed) {
              console.log('User IS subscribed.');
            } else {
              console.log('User is NOT subscribed.');
            }

            updateBtn();
        });

    }
    /*
    * change the text if the user is subscribed or not
    */
    function updateBtn() {
		if (pushButtonModal){
			if (Notification.permission === 'denied') {
				pushButtonModal.textContent = 'Notifiche Bloccate';
				pushButtonModal.disabled = true;
				updateSubscriptionOnServer(null);
				return;
			}

			if (isSubscribed) {
				pushButtonModal.textContent = 'Disabilita';
				$('.js-push-btn-modal').prop('data-target', 'pushDisableModal');
			} else {
				pushButtonModal.textContent = 'Abilita';
				$('.js-push-btn-modal').prop('data-target', 'pushEnableModal');
			}
			pushButtonModal.disabled = false;
		}
	}

    /*
     * SUBSCRIBE A USER
     */
    function subscribeUser() {
        const applicationServerKey = urlBase64ToUint8Array(applicationServerPublicKey);
        swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
        .then(function(subscription) {
            console.log('User is subscribed.');
            updateSubscriptionOnServer(subscription);
            isSubscribed = true;
            updateBtn();
        })
        .catch(function(err) {
            console.log('Failed to subscribe the user: ', err);
            updateBtn();
        });
    }
    /*
    * UNSUBSCRIBE A USER
    */
    function unsubscribeUser() {
      swRegistration.pushManager.getSubscription()
      .then(function(subscription) {
        if (subscription) {
          return subscription.unsubscribe();
        }
      })
      .catch(function(error) {
        console.log('Error unsubscribing', error);
      })
      .then(function() {
        updateSubscriptionOnServer(null);

        console.log('User is unsubscribed.');
        isSubscribed = false;

        updateBtn();
      });
    }

    /*
     *  Send subscription to application server
    */
    function updateSubscriptionOnServer(subscription) {
        if (subscription) {
            sub = JSON.stringify(subscription);
            //console.log('Salvo la sottoscrizione',subscription);
        }else{
            sub = JSON.stringify(null);
            //console.log('Elimino la sottoscrizione');
        }

        $.ajax({
            url:'{$urlSavesubscription}',
            type: "POST",
            data: sub,
            dataType: "html",
            success:function(res){
                console.log(res);
            },
            error: function(j){
                console.log('ERRORE Update subscription',j);
            }
        });
    }


JS;
Yii::app()->clientScript->registerScript('resetPwd', $resetPwd);
?>
