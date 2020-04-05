(function( mw, $, d, undefined ) {
	$( d ).on( 'click', '#ca-watch, #ca-unwatch', function( e ) {
		var $this = $(this);
		var currentPage = mw.config.get( 'wgPageName' );
		mw.loader.using( 'mediawiki.api.watch' ).done( function() {
			var api = new mw.Api();
			if( $this.attr( 'id' ) === 'ca-watch' ) {
				api.watch( currentPage ).done( function() {
					$this.attr( 'id', 'ca-unwatch' );
					$this.find( 'i' ).attr( 'class', 'bs-icon-star-full' );
				} );
			}
			if( $this.attr( 'id' ) === 'ca-unwatch' ) {
				api.unwatch( currentPage ).done( function() {
					$this.attr( 'id', 'ca-watch' );
					$this.find( 'i' ).attr( 'class', 'bs-icon-star-empty' );
				} );
			}
		} );
		e.defaultPrevented = true;
		return false;
	} );
} )( mediaWiki, jQuery, document );