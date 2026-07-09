( function ( $ ) {
    'use strict';

    var frames = {};

    $( document ).on( 'click', '.tpk-media-select', function ( e ) {
        e.preventDefault();

        var $field = $( this ).closest( '.tpk-media-field' );
        var key    = $field.data( 'field' );

        if ( ! frames[ key ] ) {
            frames[ key ] = wp.media( {
                title: 'Kép kiválasztása',
                multiple: false,
                library: { type: 'image' }
            } );

            frames[ key ].on( 'select', function () {
                var attachment = frames[ key ].state().get( 'selection' ).first().toJSON();
                var url = ( attachment.sizes && attachment.sizes.medium ) ? attachment.sizes.medium.url : attachment.url;

                $field.find( '.tpk-media-id' ).val( attachment.id );
                $field.find( '.tpk-media-preview' ).html(
                    '<img src="' + url + '" style="max-width:220px;height:auto;display:block;margin-bottom:10px;border-radius:8px;">'
                );
                $field.find( '.tpk-media-remove' ).show();
            } );
        }

        frames[ key ].open();
    } );

    $( document ).on( 'click', '.tpk-media-remove', function ( e ) {
        e.preventDefault();
        var $field = $( this ).closest( '.tpk-media-field' );
        $field.find( '.tpk-media-id' ).val( '0' );
        $field.find( '.tpk-media-preview' ).empty();
        $( this ).hide();
    } );
} )( jQuery );
