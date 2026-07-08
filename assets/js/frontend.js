( function () {
    'use strict';

    var toggle = document.querySelector( '.tpk-nav-toggle' );
    var menu   = document.getElementById( 'tpk-nav-menu' );
    if ( ! toggle || ! menu ) return;

    function closeMenu() {
        menu.classList.remove( 'tpk-nav-menu--open' );
        toggle.setAttribute( 'aria-expanded', 'false' );
    }

    function toggleMenu() {
        var isOpen = menu.classList.toggle( 'tpk-nav-menu--open' );
        toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
    }

    toggle.addEventListener( 'click', toggleMenu );

    menu.querySelectorAll( 'a' ).forEach( function ( link ) {
        link.addEventListener( 'click', closeMenu );
    } );

    document.addEventListener( 'keydown', function ( e ) {
        if ( e.key === 'Escape' ) closeMenu();
    } );
} )();
