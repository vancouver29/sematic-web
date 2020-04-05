Ext.define( 'BS.Authors.grid.Authors', {
	extend: 'Ext.grid.Panel',
	cls: 'bs-authors-flyout-authors',
	maxWidth: 600,
	pageSize : 3,
	authors: [],
	title: mw.message( 'bs-authors-flyout-title' ).plain(),
	initComponent: function () {
		this.store =  new Ext.data.Store( {
			autoLoad: true,
			fields: [ 'user_image_html', 'user_name', 'author_type' ],
			data: this.authors,
			proxy: {
				type: 'memory',
				reader: {
					type: 'json',
					rootProperty: 'authors'
				}
			}
		} );

		this.colAggregatedInfo = Ext.create( 'Ext.grid.column.Template', {
			id: 'aggregated',
			sortable: false,
			width: 400,
			tpl: new Ext.XTemplate( "<div class='bs-authors-flyout-grid-item'>" +
			"{user_image_html}" +
			"<span>{user_name}</span>" +
			"<span class='author-type'>{author_type:this.messagizeType}</span></div>",
				{
					messagizeType: function (type) {
						if( mw.message( 'bs-authors-author-type-' + type ).exists() ) {
							return mw.message( 'bs-authors-author-type-' + type ).escaped()
						}
						return type;
					}
				}
			),
			flex: 1
		} );

		this.columns = [
			this.colAggregatedInfo
		];

		this.bbar = new Ext.toolbar.Paging( {
			store: this.store
		} );

		this.callParent( arguments );
	}
});
