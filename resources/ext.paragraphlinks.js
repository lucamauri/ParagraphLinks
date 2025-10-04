/**
 * ParagraphLinks extension - Client-side functionality
 * Adds hover link icons to paragraphs for easy copying of direct links
 *
 * @license GPL-3.0-or-later
 */

( function () {
    'use strict';

    function init() {
        const content = document.querySelector( '#mw-content-text .mw-parser-output' ) ||
                        document.querySelector( '#mw-content-text' );
        if ( !content ) return;

        content.querySelectorAll( 'h2, h3, h4, h5, h6' ).forEach( heading => {
            // Find the existing ID from the nested span element
            const spanWithId = heading.querySelector( 'span[id]' );
            if ( !spanWithId || !spanWithId.id ) {
                return; // Skip headings without proper MediaWiki ID spans
            }

            const headingId = spanWithId.id;

            // Create the copy-link icon
            const icon = document.createElement( 'a' );
            icon.href = '#' + headingId;
            icon.className = 'paragraphlinks-icon';
            icon.setAttribute( 'aria-label', 'Copy link to this section' );
            icon.title = 'Copy link to this section';
            icon.innerText = '🔗';

            // Copy URL to clipboard on click
            icon.addEventListener( 'click', e => {
                e.preventDefault();
                const url = window.location.href.replace(/#.*/,'') + '#' + headingId;
                navigator.clipboard.writeText( url )
                    .then( () => {
                        if ( mw.notify ) mw.notify( 'Link copied!', { type: 'success', tag: 'paragraphlinks' } );
                    } )
                    .catch( () => {
                        if ( mw.notify ) mw.notify( 'Copy failed', { type: 'error', tag: 'paragraphlinks' } );
                    } );
            } );

            // Insert icon immediately before the heading text
            heading.insertBefore( icon, heading.firstChild );
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
}() );