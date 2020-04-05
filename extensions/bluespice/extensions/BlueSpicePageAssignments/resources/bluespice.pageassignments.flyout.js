(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.pageassignments' );

	bs.pageassignments.flyoutCallback = function( $body ) {
		var dfd = $.Deferred();
		Ext.create( 'BS.PageAssignments.flyout.Base', {
			renderTo: $body[0]
		} );
		dfd.resolve();

		return dfd.promise();
	};

})( mediaWiki, jQuery, blueSpice );
