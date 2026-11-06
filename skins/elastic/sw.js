//remember to increment the version # when you update the service worker
const version = "1.00",
    preCache = "PRECACHE-" + version,
    cacheList = [ "/" ];

/*
create a list (array) of urls to pre-cache for your application
*/

/*  Service Worker Event Handlers */

self.addEventListener( "install", function ( event ) {

    console.log( "Installing the service worker!" );

    self.skipWaiting();

    event.waitUntil(

        caches.open( preCache )
        .then( cache => {

            cache.addAll( cacheList );

        } )

    );

} );

self.addEventListener( "activate", function ( event ) {

    event.waitUntil(

        //wholesale purge of previous version caches
        caches.keys().then( cacheNames => {
            cacheNames.forEach( value => {

                if ( value.indexOf( version ) < 0 ) {
                    caches.delete( value );
                }

            } );

            console.log( "service worker activated" );

            return;

        } )

    );

} );

self.addEventListener( "fetch", function ( event ) {

    event.respondWith(

        fetch( event.request )

        /* check the cache first, then hit the network */
        /*
                caches.match( event.request )
                .then( function ( response ) {

                    if ( response ) {
                        return response;
                    }

                    return fetch( event.request );
                } )
        */
    );

} );

