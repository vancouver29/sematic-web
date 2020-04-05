Ext.define( 'BS.Statistics.panel.List', {
	extend: 'Ext.grid.Panel',
	headerPosition: 'bottom',

	bsPayload: {},

	initComponent: function() {
		this.setTitle( this.bsPayload.label );

		this.store = new Ext.data.JsonStore( {
			fields: this.bsPayload.data.fields,
			data: this.bsPayload.data.list.items,
			pageSize: 10,
			remoteSort: true,
			proxy: {
				type: 'memory',
				enablePaging: true
			}
		} );

		var columns = this.bsPayload.data.columns;
		columns[0].width = 250;
		columns[0].flex = 0;

		this.columns = {
			items: columns,
			defaults: {
				flex: 1
			}
		};

		this.dockedItems = [{
			xtype: 'pagingtoolbar',
			store: this.store,
			dock: 'bottom',
			displayInfo: true
		}];

		return this.callParent( arguments );
	}
});