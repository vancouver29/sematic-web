(function( mw, $, bs, d, undefined ) {
	$( d ).on( 'click', '.bs-articleinfo-pagetool-subpages', function( e ) {
		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.onReady( function() {
				var root = mw.config.get( 'wgPageName' );
				if( mw.config.get( 'wgNamespaceNumber' ) === bs.ns.NS_MAIN ) {
					root = ':' + root;
				}
				var diag = Ext.create( 'BS.ArticleInfo.dialog.Subpages', {
					rootPage: root
				} );
				diag.show();
			} );
		} );

		e.defaultPrevented = true;
		return false;
	} );

})( mediaWiki, jQuery, blueSpice, document );
