bs.util.registerNamespace( 'bs.insertlink.ui' );

bs.insertlink.ui.MWLocalFileSystemLinkAnnotationWidget = function BsInsertlinkUiMWLocalFileSystemLinkAnnotationWidget() {
	bs.insertlink.ui.MWLocalFileSystemLinkAnnotationWidget.super.apply( this, arguments );
};

OO.inheritClass( bs.insertlink.ui.MWLocalFileSystemLinkAnnotationWidget, ve.ui.MWExternalLinkAnnotationWidget );

bs.insertlink.ui.MWLocalFileSystemLinkAnnotationWidget.static.createExternalLinkInputWidget = function ( config ) {
	return new OO.ui.TextInputWidget( $.extend( {}, config, {
		icon: 'bsLocalFileSystemLink',
		validate: function ( text ) {
			return !!ve.init.platform.getExternalLinkUrlProtocolsRegExp().exec( text.trim() );
		}
	} ) );
};
