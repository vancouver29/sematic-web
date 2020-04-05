( function( mw, $, bs, undefined ) {
	Ext.onReady( function() {
		Ext.create( 'BS.Readers.PathPanel', {
			renderTo: 'bs-readerspath-grid'
		} );
	} );
} )( mediaWiki, jQuery, blueSpice );
