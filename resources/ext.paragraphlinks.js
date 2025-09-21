/**
 * ParagraphLinks extension - Client-side functionality
 * Adds hover link icons to paragraphs for easy copying of direct links
 *
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	const util = require( 'mediawiki.util' );

	/**
	 * Generate a unique anchor ID for a paragraph
	 * @param {Element} paragraph The paragraph element
	 * @param {number} index The paragraph index
	 * @return {string} Unique anchor ID
	 */
	function generateAnchorId( paragraph, index ) {
		// Try to get meaningful text from the paragraph
		const text = paragraph.textContent.trim();
		
		if ( text.length > 0 ) {
			// Create a slug from the first few words
			const words = text.split( /\\s+/ ).slice( 0, 5 );
			const slug = words.join( '-' )
				.toLowerCase()
				.replace( /[^a-z0-9\\-]/g, '' )
				.replace( /-+/g, '-' )
				.replace( /^-|-$/g, '' );
			
			if ( slug.length > 0 ) {
				return 'p-' + slug;
			}
		}
		
		// Fallback to paragraph index
		return 'p-' + index;
	}

	/**
	 * Copy text to clipboard
	 * @param {string} text Text to copy
	 * @return {Promise<boolean>} Success status
	 */
	async function copyToClipboard( text ) {
		try {
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				await navigator.clipboard.writeText( text );
				return true;
			}
			
			// Fallback for older browsers
			const textArea = document.createElement( 'textarea' );
			textArea.value = text;
			textArea.style.position = 'fixed';
			textArea.style.opacity = '0';
			document.body.appendChild( textArea );
			textArea.focus();
			textArea.select();
			
			const successful = document.execCommand( 'copy' );
			document.body.removeChild( textArea );
			
			return successful;
		} catch ( err ) {
			console.error( 'Failed to copy text: ', err );
			return false;
		}
	}

	/**
	 * Show a temporary notification
	 * @param {string} message Message to show
	 * @param {string} type Notification type ('success' or 'error')
	 */
	function showNotification( message, type ) {
		if ( typeof mw.notify === 'function' ) {
			mw.notify( message, { type: type } );
		} else {
			// Fallback notification
			const notification = document.createElement( 'div' );
			notification.textContent = message;
			notification.className = 'paragraphlinks-notification paragraphlinks-notification-' + type;
			notification.style.cssText = `
				position: fixed;
				top: 20px;
				right: 20px;
				padding: 10px 15px;
				background: ${type === 'success' ? '#00af89' : '#d73527'};
				color: white;
				border-radius: 4px;
				z-index: 10000;
				font-size: 14px;
			`;
			
			document.body.appendChild( notification );
			
			setTimeout( function () {
				if ( notification.parentNode ) {
					notification.parentNode.removeChild( notification );
				}
			}, 3000 );
		}
	}

	/**
	 * Create and configure the link icon element
	 * @param {string} anchorId The anchor ID to link to
	 * @return {Element} The link icon element
	 */
	function createLinkIcon( anchorId ) {
		const linkIcon = document.createElement( 'a' );
		linkIcon.href = '#' + anchorId;
		linkIcon.className = 'paragraphlinks-icon';
		linkIcon.setAttribute( 'aria-label', mw.msg( 'paragraphlinks-copy-link' ) );
		linkIcon.setAttribute( 'title', mw.msg( 'paragraphlinks-copy-link' ) );
		linkIcon.innerHTML = '🔗';
		
		linkIcon.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			
			const fullUrl = window.location.protocol + '//' + window.location.host + 
						   window.location.pathname + window.location.search + '#' + anchorId;
			
			copyToClipboard( fullUrl ).then( function ( success ) {
				if ( success ) {
					showNotification( mw.msg( 'paragraphlinks-copied' ), 'success' );
				} else {
					showNotification( mw.msg( 'paragraphlinks-copy-failed' ), 'error' );
				}
			} );
		} );
		
		return linkIcon;
	}

	/**
	 * Initialize paragraph links functionality
	 */
	function init() {
		// Find all paragraphs in the main content area
		const contentArea = document.querySelector( '#mw-content-text' ) || 
						   document.querySelector( '.mw-parser-output' ) ||
						   document.querySelector( '#content' );
		
		if ( !contentArea ) {
			return;
		}

		const paragraphs = contentArea.querySelectorAll( 'p' );
		const existingIds = new Set();
		
		// Collect existing IDs to avoid conflicts
		document.querySelectorAll( '[id]' ).forEach( function ( element ) {
			existingIds.add( element.id );
		} );

		paragraphs.forEach( function ( paragraph, index ) {
			// Skip empty paragraphs or paragraphs with very little content
			if ( paragraph.textContent.trim().length < 10 ) {
				return;
			}
			
			// Skip if paragraph already has an ID
			if ( paragraph.id ) {
				return;
			}
			
			// Generate unique anchor ID
			let anchorId = generateAnchorId( paragraph, index );
			let counter = 1;
			
			// Ensure uniqueness
			while ( existingIds.has( anchorId ) ) {
				anchorId = generateAnchorId( paragraph, index ) + '-' + counter;
				counter++;
			}
			
			existingIds.add( anchorId );
			
			// Add anchor to paragraph
			paragraph.id = anchorId;
			
			// Create wrapper div for the paragraph and link icon
			const wrapper = document.createElement( 'div' );
			wrapper.className = 'paragraphlinks-wrapper';
			
			// Move paragraph into wrapper
			paragraph.parentNode.insertBefore( wrapper, paragraph );
			wrapper.appendChild( paragraph );
			
			// Create and add link icon
			const linkIcon = createLinkIcon( anchorId );
			wrapper.appendChild( linkIcon );
		} );
	}

	// Initialize when DOM is ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

}() );