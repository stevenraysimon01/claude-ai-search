( function () {
    'use strict';

    function init() {
        document.querySelectorAll( '.claude-ai-search-widget' ).forEach( function ( widget ) {
            // Avoid double-init in editor
            if ( widget.classList.contains( 'is-editor-preview' ) ) return;
            if ( widget.dataset.initialized ) return;
            widget.dataset.initialized = 'true';

            var input      = widget.querySelector( '.cas-input' );
            var button     = widget.querySelector( '.cas-button' );
            var answerBox  = widget.querySelector( '.cas-answer' );
            var sourcesBox = widget.querySelector( '.cas-sources' );
            var showSrcs   = widget.dataset.showSources === 'true';

            function doSearch() {
                var query = input.value.trim();
                if ( ! query ) return;

                // UI: loading state
                button.disabled   = true;
                button.textContent = '…';
                answerBox.hidden  = false;
                answerBox.innerHTML = '<span class="cas-loading">Searching your content…</span>';
                sourcesBox.hidden = true;
                sourcesBox.innerHTML = '';

                fetch( ClaudeAISearch.restUrl, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce':   ClaudeAISearch.nonce,
                    },
                    body: JSON.stringify( { query: query } ),
                } )
                .then( function ( res ) { return res.json(); } )
                .then( function ( data ) {
                    if ( data.code ) {
                        // WP_Error
                        answerBox.innerHTML = '<span class="cas-error">Sorry, something went wrong. Please try again.</span>';
                        return;
                    }

                    // Render answer (convert newlines to <br> for readability)
                    answerBox.innerHTML = '<div class="cas-answer-text">' +
                        escapeHtml( data.answer ).replace( /\n/g, '<br>' ) +
                        '</div>';

                    // Render sources
                    if ( showSrcs && data.sources && data.sources.length ) {
                        var html = '<p class="cas-sources-label">Sources:</p><ul class="cas-sources-list">';
                        data.sources.forEach( function ( s ) {
                            html += '<li><a href="' + escapeHtml( s.url ) + '" target="_blank" rel="noopener">' +
                                    escapeHtml( s.title ) + '</a></li>';
                        } );
                        html += '</ul>';
                        sourcesBox.innerHTML = html;
                        sourcesBox.hidden    = false;
                    }
                } )
                .catch( function () {
                    answerBox.innerHTML = '<span class="cas-error">Network error. Please check your connection and try again.</span>';
                } )
                .finally( function () {
                    button.disabled    = false;
                    button.textContent = button.dataset.originalText || 'Search';
                } );
            }

            // Store original button text
            button.dataset.originalText = button.textContent;

            button.addEventListener( 'click', doSearch );
            input.addEventListener( 'keydown', function ( e ) {
                if ( e.key === 'Enter' ) doSearch();
            } );
        } );
    }

    function escapeHtml( str ) {
        var d = document.createElement( 'div' );
        d.appendChild( document.createTextNode( str ) );
        return d.innerHTML;
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
} )();
