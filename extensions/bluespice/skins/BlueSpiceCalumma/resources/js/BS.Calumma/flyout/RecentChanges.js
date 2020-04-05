Ext.define( 'BS.Calumma.flyout.RecentChanges', {
	extend: 'BS.flyout.TabbedDataViewBase',
	requires: ['BS.store.ApiRecentChanges'],

	showAddIcon: function() {
		return false;
	},

	makeCommonStore: function() {
		return new BS.store.ApiRecentChanges();
	},

	makeDataViewThumbImageModuleName: function() {
		return 'articlepreviewimage';
	},

	makeDataViewThumbImageTitletextValue: function( dataset ) {
		return dataset.page_prefixedtext;
	},

	makeDataViewThumbnailCaptionTitle: function( dataset ) {
		return dataset.page_prefixedtext;
	},

	makeDataViewItemMetaItems: function( dataset ) {
		if( dataset.user_display_name && dataset.user_display_name !== '' ) {
			var name = dataset.user_display_name;
		}
		else {
			var name = dataset.user_name;
		}
		return [
			{ itemHtml: '<span>' + dataset.user_name + '</span><span> (' + dataset.timestamp + ')</span>' },
			{ itemHtml: '<span>' + dataset.comment_text + '</span>' }
		];
	},

	dataViewItemHasMenu: function( dataset ) {
		return true;
	},

	makeToolsLinkText: function() {
		return '';
	},

	makeTooleMenu: function( dataset ) {
		return new Ext.menu.Menu( {
			items: [{
				plain: true,
				iconCls: 'bs-icon-history',
				text: dataset.get( 'diff_link' ),
				onClick: function(){}
			}, {
				plain: true,
				iconCls: 'bs-icon-history',
				text: dataset.get( 'hist_link' ),
				onClick: function(){}
			}]
		});
	},

	makeGridPanelColums: function() {
		return [{
			header: mw.message( 'bs-calumma-recentchanges-column-header-title' ).plain(),
			dataIndex: 'page_prefixedtext',
			flex: 1,
			filter: {
				type: 'string'
			},
			renderer: function( value, metadata, record ) {
				var diff = mw.html.element(
						'a',
						{
							'href': record.get( 'diff_url' )
						},
						mw.message( 'bs-calumma-recentchanges-diff' ).plain()
					);

				var history = mw.html.element(
						'a',
						{
							'href': record.get( 'hist_url' )
						},
						mw.message( 'bs-calumma-recentchanges-history' ).plain()
					);

				return '<div><span class="title">' + record.get( 'page_link' ) + '</span><span class="actions">( ' + diff + ' | ' + history + ' )</span></div>';
			}
		}];
	},

	makeDataViewThumbImageRevidValue: function( values ) {
		return values.this_oldid;
	}
});
