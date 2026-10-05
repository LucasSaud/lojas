const applicationServerKey = "BL3C3gOHSRa45H9P8PFnrN7t23VyKjyezjYqhntJ-hcvdDiirCg7NioV8KrNR7e8PyEJtajXNgesyGHzkazDXiE";
let pushButton = document.querySelector('.js-push-btn');

let serviceWorkerRegistration = null;
let isPushSubscribed = false;

window.addEventListener('load', function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }
    if (!('PushManager' in window)) {
        return;
    }
    navigator.serviceWorker.register('serviceworker.js')
    .then(function(registration){
        serviceWorkerRegistration = registration;
        initializePushMessage();
    }).catch(function(error) {
        console.error('Unable to register service worker.', error);
    });
});

function initializePushMessage() {
    serviceWorkerRegistration.pushManager.getSubscription()
        .then(function (subscription) {
            fetch('save-subscription.php?checknotification=false', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            })
            .then(function (response) {
                if (!response.ok) {
                    reject('Bad status code from server')
                }
                return response.json();
            })
            .then(function (responseData) {
                if (!responseData.status || responseData.status !== 'ok') {
                    reject(responseData.status);
                }
                resolve(responseData.status);
            })
            .catch(function (error){
                reject(error);
            });
            
            isPushSubscribed = !(subscription === null);
            updateBtn();
        });
}

function unsubscribeUserFromPush() {
    pushButton.disabled = true;

    serviceWorkerRegistration.pushManager.getSubscription()
    .then(function(subscription) {
      if (subscription) {
        subscription.unsubscribe();
        return subscription;
      }
    })
    .then(function(subscription) {
      updateSubscriptionOnServer(subscription, false);
 
      isPushSubscribed = false;
      updateBtn();
    })
    .catch(function(error) {
      alert('Error ao ativar notificações');
    });
}

function updateBtn() {
    if (Notification.permission === 'denied') {
        //pushButton.textContent = 'Notificações bloqueadas.';
        //pushButton.disabled = true;
        return;
    }

    if (isPushSubscribed) {
        //pushButton.textContent = 'Desativar notificações';
    } else {
        //pushButton.textContent = 'Ativar notificações';
        getNotificationPermission().then(function (status) {
            subscribeUserToPush()
            .then(function () {
                updateBtn();
            })
            .catch(function (error) {
                //alert('Error:' + error);
            });
        }).catch(function (error) {
            if (error === "support") {
                //alert("Seu navegador não suporta notificações, tente instalar o chrome ou firefox.");
            }
            else if (error === "denied") {
                //alert('Você bloqueou as notificações.');
            }
            else if(error === "default"){
                updateBtn();
                //alert('Você fechou a janela de permissão, não foram ativadas as notificações.');
            }
            else {
                //alert('Houve um problema, tente novamente.');
            }
        });
    }
    pushButton.disabled = false;
}

function getNotificationPermission() {
    return new Promise(function (resolve, reject) {
        if(!("Notification" in window)){
            reject('support');
        }
        else{
            Notification.requestPermission(function (permission) {
                (permission === 'granted')? resolve(permission): reject(permission);
            });
        }
    });
}

function subscribeUserToPush() {
    const subscribeOptions = {
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(applicationServerKey)
    };
    return new Promise(function (resolve, reject) {
        serviceWorkerRegistration.pushManager.subscribe(subscribeOptions)
        .then(function (subscription) {
            updateSubscriptionOnServer(subscription)
            .then(function (status) {
                isPushSubscribed = true;
                resolve(status);
            })
            .catch(function (error) {
                reject(error);
            })
        }).catch(function (error) {
            reject(error);
        });
    });
}

function updateSubscriptionOnServer(subscription = null, subscribe = true) {
    return new Promise(function (resolve, reject) {
        let extra = (subscribe)? '?subscribe': '?unsubscribe';
        fetch('save-subscription.php?checknotification=true', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(subscription)
        })
        .then(function (response) {
            if (!response.ok) {
                reject('Bad status code from server')
            }
            return response.json();
        })
        .then(function (responseData) {
            if (!responseData.status || responseData.status !== 'ok') {
                reject(responseData.status);
            }
            resolve(responseData.status);
        })
        .catch(function (error){
            reject(error);
        });
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}