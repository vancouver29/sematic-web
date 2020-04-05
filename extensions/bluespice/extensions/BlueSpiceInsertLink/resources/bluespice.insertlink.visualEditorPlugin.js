bs.vec.registerComponentPlugin(
	bs.vec.components.LINK_ANNOTATION_INSPECTOR,
	function( component ) {
		component.linkTypeIndex.addTabPanels( [
			new OO.ui.TabPanelLayout( 'localfilesystem', {
				label: ve.msg( 'bs-insertlink-tab-ext-file' ),
				expanded: false,
				scrollable: false,
				padded: true
			} )
		] );

		component.localFileSystemAnnotationInput =
			new bs.insertlink.ui.MWLocalFileSystemLinkAnnotationWidget();

		component.linkTypeIndex.getTabPanel( 'localfilesystem' ).$element.append(
			component.localFileSystemAnnotationInput.$element
		);
	}
);