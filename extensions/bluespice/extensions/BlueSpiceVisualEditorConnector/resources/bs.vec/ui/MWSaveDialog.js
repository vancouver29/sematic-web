bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWSaveDialog = function BsVecUiMWSaveDialog() {
	bs.vec.ui.MWSaveDialog.super.apply( this, arguments );
};

OO.inheritClass( bs.vec.ui.MWSaveDialog, ve.ui.MWSaveDialog );

bs.vec.ui.MWSaveDialog.prototype.initialize = function () {
	bs.vec.ui.MWSaveDialog.super.prototype.initialize.call( this );

	this.initComponentPlugins();

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		plugin.initialize();
	}
};

bs.vec.ui.MWSaveDialog.prototype.initComponentPlugins = function() {
	this.componentPlugins = [];

	var pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.SAVE_DIALOG
	);

	for( var i = 0; i < pluginCallbacks.length; i++ ) {
		var callback = pluginCallbacks[i];
		this.componentPlugins.push( callback( this ) );
	}
};

bs.vec.ui.MWSaveDialog.prototype.getActionProcess = function ( action ) {
	var parentProcess = bs.vec.ui.MWSaveDialog.super.prototype.getActionProcess.apply( this, arguments );

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		plugin.getActionProcess( parentProcess, action );
	}

	if( action === 'save' ) {
		parentProcess.next( function() {
			window.location = mw.util.getUrl(
				mw.config.get( 'wgPageName' )
			);
		} );
	}

	return parentProcess;
};

bs.vec.ui.MWSaveDialog.prototype.swapPanel = function ( panel, noFocus ) {
	bs.vec.ui.MWSaveDialog.super.prototype.swapPanel.apply( this, arguments );

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		plugin.swapPanel( panel, noFocus );
	}
}