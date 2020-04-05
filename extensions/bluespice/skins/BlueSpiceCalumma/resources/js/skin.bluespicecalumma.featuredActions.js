( function ( mw, $, bs, d, undefined ) {
	var specialNamespace = ( mw.config.get( 'wgContentNamespaces' ).indexOf( mw.config.get( 'wgNamespaceNumber' ) ) === -1 );

	$( document ).on( 'click', '.bs-fa-new-page', function () {
		mw.loader.using( 'ext.bluespice.extjs' ).done( function () {
			Ext.Loader.setPath( 'BS.Calumma', mw.config.get( 'stylepath' )
					+ '/BlueSpiceCalumma/resources/js/BS.Calumma' );

			Ext.onReady( function () {
				var siteNamespace;
				if ( specialNamespace || mw.config.get( 'wgCanonicalNamespace' ) === '' ) {
					siteNamespace = '';
				} else {
					siteNamespace = mw.config.get( 'wgCanonicalNamespace' ) + ':';
				}
				var dlg = Ext.create( 'BS.Calumma.dialog.NewPage', {
					namespace: siteNamespace
				} );

				dlg.on( 'ok', function ( sender, pageName ) {
					if ( pageName ) {
						window.location.href = mw.util.getUrl( pageName, { action: 'view' } );
					}
				} );
				dlg.show();
			} );
		} );
	} );

	$( document ).on( 'click', '.bs-fa-new-subpage', function () {
		mw.loader.using( 'ext.bluespice.extjs' ).done( function () {
			Ext.Loader.setPath( 'BS.Calumma', mw.config.get( 'stylepath' )
					+ '/BlueSpiceCalumma/resources/js/BS.Calumma' );

			Ext.onReady( function () {
				var currentPageName = mw.config.get( 'wgPageName' );
				dlg = Ext.create( 'BS.Calumma.dialog.NewSubPage', {
					basePage: currentPageName
				} );

				dlg.on( 'ok', function ( sender, data ) {
					var data = currentPageName + '/' + data;
					window.location.href = mw.util.getUrl( data, { action: 'view' } );
				} );
				dlg.show();
			} );
		} );
	} );
} )( mediaWiki, jQuery, blueSpice, document );
