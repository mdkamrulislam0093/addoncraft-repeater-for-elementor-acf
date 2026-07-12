( function () {
	'use strict';

	function initRepeaterSliders() {
		document.querySelectorAll( '.repefoel_acf_repeater_rs_sliders.swiper' ).forEach( function ( el ) {
			if ( el._repefoelSwiper ) {
				return; // already initialized
			}

			var raw = el.getAttribute( 'data-swiper_settings' );
			if ( ! raw ) {
				return;
			}

			var settings;
			try {
				settings = JSON.parse( raw );
			} catch ( e ) {
				return;
			}

			if ( typeof Swiper === 'undefined' ) {
				return;
			}

			el._repefoelSwiper = new Swiper( el, settings );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initRepeaterSliders );
	} else {
		initRepeaterSliders();
	}

	// Re-init after Elementor frontend is ready (handles editor preview reloads).
	if ( window.elementorFrontend ) {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', initRepeaterSliders );
	} else {
		document.addEventListener( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', initRepeaterSliders );
			}
		} );
	}
} )();
