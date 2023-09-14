// import workbox 
importScripts('workbox-sw.prod.v2.1.3.js');

// import IndexedDB
importScripts('bundles/sw/src/js/idb.js');
importScripts('bundles/sw/src/js/utility.js');

var GOOGLE_FONT = 'google-fonts';
var DYNAMIC = 'dynamic';
var OFFLINE_PAGE = '/offline.html';

const workboxSW = new self.WorkboxSW();

// recupera tutto ciò che è nella regular expression e la inserisce nella cache google-fonts
workboxSW.router.registerRoute(/.*(?:googleapis|gstatic)\.com.*$/, workboxSW.strategies.staleWhileRevalidate({
    cacheName: GOOGLE_FONT,
    cacheExpiration: {
        maxEntries: 10,
        maxAgeSeconds: 60 * 60 * 24 * 30
    }
}));


// funzione per ELIMINARE LA CACHE
function trimCache(cacheName, maxItems) {
    console.log('[Service Worker] trimming caches...');

    caches.open(cacheName)
        .then(function (cache) {
            return cache.keys()
                .then(function (keys) {
                    if (keys.lenght > maxItems) {
                        cache.delete(keys[0])
                            .then(trimCache(cacheName, maxItems));
                    }
                });
        });
}


// Funzione Fix per apache
function cleanResponse(response) {
    const clonedResponse = response.clone();

    // Not all browsers support the Response.body stream, so fall back to reading
    // the entire body into memory as a blob.
    const bodyPromise = 'body' in clonedResponse ?
        Promise.resolve(clonedResponse.body) :
        clonedResponse.blob();

    return bodyPromise.then((body) => {
        // new Response() is happy when passed either a stream or a Blob.
        return new Response(body, {
            headers: clonedResponse.headers,
            status: clonedResponse.status,
            statusText: clonedResponse.statusText,
        });
    });
}

workboxSW.router.registerRoute(function (routeData) {
    return (routeData); //.event.request.headers.get('accept')); //.includes('text/html'));
}, function (args) {

    // console.log('[SW args]', args);
    var parser = new URL(args.event.request.url);
    // console.log('[SW Parser]', parser.pathname);

    if (args.event.request.mode === 'navigate' && navigator.onLine === false) {
        // Uh-oh, we navigated to a page while offline. Let's show our default page.
        return caches.match(OFFLINE_PAGE)
            .then(function (res) {
                return res;
            });
    }
    /**
     * FIXES 'enablePrettyUrl' => true
     * 
     * LA soluzione è di aggiungere il path dei singoli controller senza le actions
     * 
     */
    
    if (parser.pathname.includes('/site/') 
        || parser.pathname.includes('/admin/') 
        || parser.pathname.includes('/callback/') 
        || parser.pathname.includes('/invoices/') 
        || parser.pathname.includes('/logs/') 
        || parser.pathname.includes('/merchants/') 
        || parser.pathname.includes('/pos/') 
        || parser.pathname.includes('/privileges/') 
        || parser.pathname.includes('/settings/') 
        || parser.pathname.includes('/stores/') 
        || parser.pathname.includes('/users/') 
    ) {
        // console.log('[SW Parser] do not cached');
        console.log('[SW NO Parser]', parser.pathname);
        return;
    }
    
    console.log('[SW Parser]', parser.pathname);
    // console.log('[SW Parser] cached');
    return caches.match(args.event.request)
        .then(function (response) {
            if (response) {
                // Inizio Fix per apache
                if (response.redirected) {
                    return cleanResponse(response);
                } else {
                    return response;
                }
                // END Fix per apache
            } else {
                return fetch(args.event.request)
                    .then(function (res) {
                        return caches.open(DYNAMIC)
                            .then(function (cache) {
                                cache.put(args.event.request.url, res.clone());
                                return res;
                            })
                    })
                    .catch(function (err) {
                        return caches.match(OFFLINE_PAGE)
                            .then(function (res) {
                                return res;
                            });
                    });
            }
        });
    

    

});


workboxSW.precache([]);

// listener per ricevimento messaggi
self.addEventListener('message', function (event) {
    // il mesaggio è 'skipWaiting'
    if (event.data.action === 'skipWaiting') {
        self.skipWaiting();
        trimCache(GOOGLE_FONT, 0);
        trimCache(DYNAMIC, 0);
    }
});

// listener per le notifiche push
self.addEventListener('push', function (event) {
    const info = JSON.parse(`${event.data.text()}`);
    console.log(`[Service Worker] Push has this data: ` + info);

    const sendNotification = body => {
        return self.registration.showNotification(info.title, {
            body: info.body,
            icon: info.icon,
            badge: info.badge,
            vibrate: info.vibrate,
            image: info.image,
            sound: info.sound,
            data: info.data,
            actions: info.actions,
            tag: info.tag,
            renotify: true,
            data: {
                openUrl: info.openUrl,
            },
        });
    };

    if (event.data) {
        const message = event.data.text();
        event.waitUntil(sendNotification(message));
    }
});


// listener click su evento di notifica
self.addEventListener('notificationclick', function (event) {
    console.log(`[Service Worker] notification click: ` + event.notification);

    if (typeof event.notification.data !== 'undefined') {
        var action = event.notification.data;
    } else {
        var action = JSON.parse(event.actions);
    }

    event.notification.close();
    if (event.action !== 'close') {
        console.log('handle go to url with yes button');
        event.waitUntil(clients.openWindow(action.openUrl));
    }
}, false);

// listener su chiusura notifica
self.addEventListener('notificationclose', function (event) {
    console.log(`[Service Worker] notification was closed` + event);
});
