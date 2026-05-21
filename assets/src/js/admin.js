import '../css/admin.css';

/**
 * Dismiss admin notices with the starter-plugin-notice class.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	document
		.querySelectorAll( '.starter-plugin-notice .notice-dismiss' )
		.forEach( ( button ) => {
			button.addEventListener( 'click', ( event ) => {
				event.target.closest( '.starter-plugin-notice' ).remove();
			} );
		} );
} );
