bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.TextInputMWVisualEditor = function ( config ) {
	OO.ui.MultilineTextInputWidget.call( this, config );
	var me = this;
	me.selector = config.selector || '.bs-vec-widget';
	me.visualEditor = null;
	me.config = config;
};
OO.initClass( bs.ui.widget.TextInputMWVisualEditor );
OO.inheritClass( bs.ui.widget.TextInputMWVisualEditor, OO.ui.MultilineTextInputWidget );

bs.ui.widget.TextInputMWVisualEditor.prototype.init = function() {
	// nothing to do here. Method must exist for interface reasons.
};

bs.ui.widget.TextInputMWVisualEditor.prototype.onFocus = function() {
	if( this.visualEditor ) {
		return;
	}
	this.makeVisualEditor( this.config );
	$( this.config.selector ).hide();
};

bs.ui.widget.TextInputMWVisualEditor.prototype.onBlur = function() {
	// do nothing to prevent checkValidity error
};

bs.ui.widget.TextInputMWVisualEditor.prototype.getValue = function() {
	if( !this.visualEditor ) {
		return;
	}
	return this.visualEditor.getWikiText();
};

bs.ui.widget.TextInputMWVisualEditor.prototype.setValue = function( value ) {
	return;
};

bs.ui.widget.TextInputMWVisualEditor.prototype.makeVisualEditor = function( config ) {
	var me = this;
	config = config || me.config;
	me.emit( 'editorStartup', this );
	bs.vec.createEditor( config.id, {
		renderTo: config.selector,
		value: config.value,
		format: config.format
	}).done( function( target ){
		me.visualEditor = target;
	}).then( function(){
		me.emit( 'editorStartupComplete', this );
	});
	return;
};