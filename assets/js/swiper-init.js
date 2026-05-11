/**
 * REPEFOEL Swiper Initializer
 * Replaces per-widget inline scripts. Reads data-swiper_settings JSON
 * from every .repefoel_acf_repeater_rs_sliders.swiper element and boots Swiper.
 */
( function ( $ ) {
    'use strict';

    /**
     * Initialise a single Swiper container.
     *
     * @param {HTMLElement} container  The .swiper element.
     */
    function initOne( container ) {
        if ( ! container ) return;

        // Already initialised – destroy first so settings refresh in the editor.
        if ( container.swiper ) {
            container.swiper.destroy( true, true );
        }

        if ( typeof Swiper === 'undefined' ) return;

        try {
            var opts = JSON.parse( container.getAttribute( 'data-swiper_settings' ) || '{}' );
            new Swiper( container, opts );
        } catch ( e ) {
            // Malformed JSON in data attribute – skip silently.
        }
    }

    /**
     * Initialise every un-initialised carousel on the page.
     */
    function initAll() {
        document
            .querySelectorAll( '.repefoel_acf_repeater_rs_sliders.swiper' )
            .forEach( initOne );
    }

    // ── Frontend page load ───────────────────────────────────────────────
    $( document ).ready( initAll );

    // ── Elementor editor / preview re-render ────────────────────────────
    $( window ).on( 'elementor/frontend/init', function () {
        if ( typeof elementorFrontend === 'undefined' ) return;

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/REPEFOEL_widget_repeater_carousel.default',
            function ( $scope ) {
                var container = $scope[ 0 ].querySelector(
                    '.repefoel_acf_repeater_rs_sliders.swiper'
                );
                initOne( container );
            }
        );
    } );

} )( jQuery );
