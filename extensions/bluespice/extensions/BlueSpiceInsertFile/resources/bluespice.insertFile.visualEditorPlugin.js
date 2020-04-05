bs.vec.registerComponentPlugin(
	bs.vec.components.MEDIA_DIALOG,
	function( component ) {

		var advancedSearchTab = new OO.ui.TabPanelLayout( 'bs-insertfile-advanced-search-panel', {
			label: ve.msg( 'bs-insertfile-advanced-search-panel-label' ),
			classes: [
				'bs-insertfile-advanced-search-panel-container'
			]
		} );
		var fileRepoGrid = null;

		component.searchTabs.addTabPanels( [ advancedSearchTab ] );

		/**
		 * Unfortunately rendering an ExtJS component within the constructor of an `OO.ui.Widget`
		 * is not always possible, as `OO.ui.Widget::$element` may not be appended to the DOM yet.
		 * Therefore we render the ExtJS component when the user actually opens the tab.
		 */
		component.searchTabs.on( 'set', function( selectedTab ) {
			if( selectedTab === advancedSearchTab ) {
				component.setSize( 'larger' );
				if( fileRepoGrid === null ) {
					mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
						Ext.onReady( function() {
							fileRepoGrid = Ext.create( 'BS.grid.FileRepo', {
								renderTo: selectedTab.$element[0],
								height: selectedTab.$element.height(),
								width: selectedTab.$element.width(),
								uploaderCfg: {}
							} );

							fileRepoGrid.on( 'select', function( sender, record, eOpts ) {
								//required fields extracted from https://github.com/wikimedia/mediawiki-extensions-VisualEditor/blob/b8bcba8cbeb38f9be8232c77140b04e8da1040cc/modules/ve-mw/ui/dialogs/ve.ui.MWMediaDialog.js#L476-L718
								imageInfo = {
									title: record.get( 'page_prefixed_text' ),
									extmetadata: [], //Not available from 'bs-filerepo-store' API
									user: record.get( 'file_user_text' ),
									timestamp: record.get( 'file_timestamp' ),
									descriptionurl: record.get( 'page_prefixed_text' ),
									url: record.get( 'file_url' ),
									mediatype: record.get( 'file_mediatype' ),
									width: record.get( 'file_width' ),
									height: record.get( 'file_height' ),
									thumburl: record.get( 'file_thumbnail_url' ),
									thumbwidth: 80 //Hardcoded in 'bs-filerepo-store'
								};

								component.chooseImageInfo( imageInfo );
							})
						} );
					} );
				}
			}
		} );
	}
);