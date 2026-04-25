/**
 * ParagraphLinks extension - Client-side functionality
 * Adds hover link icons to section headings for easy copying of direct links
 *
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	function init() {
		// Target only the parsed content area, not the rest of the page
		const content = document.querySelector( '#mw-content-text .mw-parser-output' ) ||
		                document.querySelector( '#mw-content-text' );
		if ( !content ) {
			return;
		}

		content.querySelectorAll( 'h2, h3, h4, h5, h6' ).forEach( heading => {
			// MediaWiki places the anchor ID on a nested <span> inside each heading
			const spanWithId = heading.querySelector( 'span[id]' );
			if ( !spanWithId || !spanWithId.id ) {
				// Skip headings without a proper MediaWiki anchor span
				return;
			}

			const headingId = spanWithId.id;

			// Build the copy-link icon element
			const icon = document.createElement( 'a' );
			icon.href = '#' + headingId;
			icon.className = 'paragraphlinks-icon';
			// Use i18n messages for all user-visible strings
			icon.setAttribute( 'aria-label', mw.msg( 'paragraphlinks-copy-link' ) );
			icon.title = mw.msg( 'paragraphlinks-copy-link' );
			icon.innerText = '🔗';

			// Copy the full section URL to the clipboard on click
			icon.addEventListener( 'click', e => {
				e.preventDefault();
				const url = window.location.href.replace( /#.*/, '' ) + '#' + headingId;
				navigator.clipboard.writeText( url )
					.then( () => {
						// Notify the user of success
						mw.notify(
							mw.msg( 'paragraphlinks-copied' ),
							{ type: 'success', tag: 'paragraphlinks' }
						);
					} )
					.catch( () => {
						// Notify the user of failure
						mw.notify(
							mw.msg( 'paragraphlinks-copy-failed' ),
							{ type: 'error', tag: 'paragraphlinks' }
						);
					} );
			} );

			// Prepend the icon so it appears to the left of the heading text
			heading.insertBefore( icon, heading.firstChild );
		} );
	}

	// Run after the DOM is ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );