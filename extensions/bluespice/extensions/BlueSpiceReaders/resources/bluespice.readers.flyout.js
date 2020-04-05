(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.readers.flyout' );

	bs.readers.flyout.makeItems = function() {
		return {
			centerLeft: [
				Ext.create( 'BS.Readers.grid.Readers', {} )
			]
		}
	};

})( mediaWiki, jQuery, blueSpice );
