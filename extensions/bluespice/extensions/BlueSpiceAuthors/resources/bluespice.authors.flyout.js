Ext.onReady( function() {
	Ext.Loader.setPath(
		'BS.Authors',
		bs.em.paths.get( 'BlueSpiceAuthors' ) + '/resources/BS.Authors'
	);
});

(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.authors.flyout' );

	bs.authors.flyout.makeItems = function() {
		if( mw.config.get( 'bsgPageAuthors' ) !== null ) {
			return {
				centerRight: [
					Ext.create( 'BS.Authors.grid.Authors', {
						authors: mw.config.get( 'bsgPageAuthors' )
					} )
				]
			}
		}

		return {};
	};

})( mediaWiki, jQuery, blueSpice );
