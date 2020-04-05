bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWMediaDialog = function BsVecUiMWMediaDialog( config ) {
	bs.vec.ui.MWMediaDialog.super.call( this, config );
};

OO.inheritClass( bs.vec.ui.MWMediaDialog, ve.ui.MWMediaDialog );

bs.vec.ui.MWMediaDialog.prototype.initialize = function () {
	bs.vec.ui.MWMediaDialog.super.prototype.initialize.call( this );

	this.runComponentPlugins();
};

bs.vec.ui.MWMediaDialog.prototype.runComponentPlugins = function() {
	var pluginCallbacks = bs.vec.getComponentPlugins(
			bs.vec.components.MEDIA_DIALOG
	);

	for( var i = 0; i < pluginCallbacks.length; i++ ) {
		var callback = pluginCallbacks[i];
		callback( this );
	}
};

bs.vec.ui.MWMediaDialog.prototype.switchPanels = function ( panel, stopSearchRequery ) {
	if( panel !== 'search' ) {
		bs.vec.ui.MWMediaDialog.parent.prototype.switchPanels.apply( this, [ panel, stopSearchRequery ] );
	} else {
		this.setSize( 'larger' );
		this.selectedImageInfo = null;
		if ( !stopSearchRequery ) {
			this.search.getQuery().setValue( '*' );
			this.search.getQuery().focus().select();
		}
		// Set the edit panel
		this.panels.setItem( this.mediaSearchPanel );
		this.searchTabs.setTabPanel( 'search' );
		this.searchTabs.toggleMenu( true );
		this.actions.setMode( this.imageModel ? 'change' : 'select' );
		// Layout pending items
		this.search.runLayoutQueue();
	}
	this.currentPanel = panel || 'imageinfo';
};