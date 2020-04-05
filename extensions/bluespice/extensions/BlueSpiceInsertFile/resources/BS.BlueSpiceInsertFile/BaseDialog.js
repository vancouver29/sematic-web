Ext.define( 'BS.BlueSpiceInsertFile.BaseDialog', {
	extend: 'MWExt.Dialog',
	requires: [
		'MWExt.form.field.Search', 'BS.model.File','BS.dialog.Upload'
	],
	modal: true,
	bodyPadding: 0,
	width: 800,
	height: 700,
	layout: 'border',

	storeFileType: 'file',
	isSetData: false,

	configPanel: {
		fieldDefaults: {
			labelAlign: 'right',
			anchor: '95%'
		},
		collapsible: true,
		collapsed: true,
		title: mw.message('bs-insertfile-details-title').plain(),
		region: 'south',
		layout: 'anchor',
		items: []
	},

	afterInitComponent: function() {
		this.conf = {
			columns: {
				items: [
				{
					dataIndex: 'file_thumbnail_url',
					renderer: this.renderThumb,
					width: 56,
					sortable: false
				},{
					text: mw.message('bs-insertfile-filename').plain(),
					dataIndex: 'file_display_text',
					flex: 1,
					filter: {
						 type: 'string'
					}
				},{
					text: mw.message('bs-insertfile-filesize').plain(),
					dataIndex: 'file_size',
					renderer:this.renderSize,
					width: 100,
					filter: {
						 type: 'number'
					}
				},{
					text: mw.message('bs-insertfile-lastmodified').plain(),
					dataIndex: 'file_timestamp',
					renderer:this.renderLastModified,
					width: 150,
					filter: {
						 type: 'date'
					}
				},
				this.makeMimeTypeColumn()
			],
				defaults: {
					tdCls: 'bs-if-cell',
					filterable: true
				}
			}
		};

		this.stImageGrid = Ext.create('Ext.data.Store', {
			pageSize: 25,
			proxy: {
				type: 'ajax',
				url: mw.util.wikiScript('api'),
				reader: {
					type: 'json',
					root: 'results',
					idProperty: 'file_name',
					totalProperty: 'total'
				},
				extraParams: {
					format: 'json',
					action: 'bs-filebackend-store'
				}
			},
			remoteFilter: true,
			autoLoad: true,
			model: 'BS.model.File',
			sortInfo: {
				field: 'file_timestamp',
				direction: 'ASC'
			}
		});
		this.stImageGrid.on( 'load', this.onStImageGridLoad, this );

		this.sfFilter = new MWExt.form.field.Search( {
			fieldLabel: mw.message('bs-insertfile-labelfilter').plain(),
			width: 500,
			labelWidth: 50,
			store: this.stImageGrid,
			paramName: 'file_name',
			listeners: {
				change: function( field, newValue, oldValue, eOpts ) {
					field.onTrigger2Click();
					return true;
				}
			}
		});

		this.dlgUpload = new BS.dialog.Upload({
			title: mw.message('bs-insertfile-labelupload').plain(),
			id: this.getId()+'-upload-dlg',
			allowedFileExtensions: this.allowedFileExtensions,
			uploadPanelCfg: {
				defaultFileNamePrefix: mw.config.get( 'wgTitle' ) //Without namespace prefix
			}
		});

		this.dlgUpload.on( 'ok', this.dlgUploadOKClick, this );

		this.btnUpload = Ext.create('Ext.Button',{
			tooltip: mw.message('bs-insertfile-labelupload').plain(),
			glyph: true,
			iconCls: 'bs-icon-upload'
		});

		this.btnUpload.on( 'click', this.btnUploadClick, this );

		var toolBarItems = [
			this.sfFilter
		];

		if( mw.config.get('bsEnableUploads') ) {
			toolBarItems.push( '->' );
			toolBarItems.push( this.btnUpload );
		}

		this.tbGridTools = [
			new Ext.toolbar.Toolbar( {
				dock: 'top',
				items: toolBarItems
			} ),
			new Ext.toolbar.Paging( {
				dock: 'bottom',
				store: this.stImageGrid,
				displayInfo: true
			} )
		];

		this.gdImages = Ext.create('Ext.grid.Panel', {
			region: 'center',
			collapsible: false,
			store: this.stImageGrid,
			loadMask: true,
			dockedItems: this.tbGridTools,
			plugins: 'gridfilters',
			viewConfig: {
				trackOver: false,
				emptyText: mw.message('bs-insertfile-nomatch').plain()
			},
			columns: this.conf.columns
		});

		this.gdImages.on( 'select', this.onGdImagesSelect, this );

		this.tfFileName = Ext.create('Ext.form.TextField', {
			readOnly: true,
			fieldLabel: mw.message('bs-insertfile-filename').plain()
		});

		this.tfLinkText = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-insertfile-linktext').plain()
		});

		this.rgNsText = Ext.create('Ext.form.RadioGroup', {
			fieldLabel: mw.message('bs-insertfile-nstext').plain(),
			layout: {
				type: 'hbox'
			},
			items: this.makeRgNsTextItems()
		});

		this.configPanel.items.unshift(this.rgNsText);
		this.configPanel.items.unshift(this.tfFileName);
		this.configPanel.items.unshift(this.tfLinkText);
		this.tfFileName.on('change', this.onTfFileNameChange, this);

		this.configPanel.height = 450;
		this.pnlConfig = Ext.create('Ext.form.Panel', this.configPanel );
		this.pnlConfig.on('expand', this.onPnlConfigExpand, this);

		this.items = [
			this.gdImages,
			this.pnlConfig
		];

		$(document).trigger("BSInsertFileInsertBaseDialogAfterInit", [this, this.items]);
		this.callParent(arguments);
	},

	makeRgNsTextItems: function() {
			return [{
				boxLabel: mw.message('bs-insertfile-nstextfile').plain(),
				itemId: 'ns-text-file',
				name: 'ns-text',
				inputValue: 'file',
				checked: true,
				width: 160
			},{
				boxLabel: mw.message('bs-insertfile-nstextmedia').plain(),
				itemId: 'ns-text-media',
				name: 'ns-text',
				inputValue: 'media',
				checked: false,
				width: 160
			}]
	},

	onStImageGridLoad: function( store, records, successful, eOpts ) {
		//Only if we have a exact match selected
		if( store.filters.items.length > 0 && records.length === 1 ) {
			this.gdImages.getSelectionModel().select(0);
		}
	},

	onTfFileNameChange: function( textfield, newValue, oldValue, eOpts ){
		$(document).trigger("BSInsertFileConfigPanelFileNameChange", [this, textfield, newValue, oldValue, eOpts]);
	},

	onPnlConfigExpand: function(panel, eOpts){
		$(document).trigger("BSInsertFileConfigPanelExpand", [this, panel, eOpts]);
	},

	btnUploadClick: function( sender, event ) {
		this.dlgUpload.show();
	},

	dlgUploadOKClick: function( dialog, upload ){
		this.stImageGrid.reload();
		this.resetFilters();
		this.sfFilter.setValue( upload.filename );
	},

	getData: function() {
		var cfg = {
			title: this.tfFileName.getValue(),
			displayText: this.tfLinkText.getValue(),
			nsText: this.rgNsText.getValue()['ns-text']
		};
		return cfg;
	},

	setData: function( obj ) {
		//Reset all fields. maybe do this onOKClick
		this.sfFilter.reset();
		this.tfFileName.reset();
		this.tfLinkText.reset();
		this.rgNsText.reset();

		this.rgNsText.setValue({
			'ns-text': obj.nsText
		});

		if( obj.title ) {
			this.resetFilters();
			this.tfFileName.setValue( obj.title );
			this.sfFilter.setValue( obj.title );
			this.sfFilter.onTrigger2Click();
			this.pnlConfig.expand( false );
		}
		else{
			this.stImageGrid.clearFilter();
			this.pnlConfig.collapse();
		}

		if( obj.displayText ) {
			this.tfLinkText.setValue( obj.displayText );
		}
		this.callParent( arguments );
	},

	renderThumb: function( url, meta, record ) {
		var attribs = {
			class: 'bs-insertfile-icon',
			style: 'background-image:url('+url+')'
		};

		return mw.html.element( 'div', attribs );
	},

	renderSize: function( size ){
		return Ext.util.Format.fileSize( size );
	},

	renderLastModified: function( lastmod ){
		return Ext.Date.format( lastmod, 'd.m.Y G:i' );
	},

	onGdImagesSelect: function( grid, record, index, eOpts ){
		this.tfFileName.setValue( record.get('file_name') );
		this.pnlConfig.expand();
	},

	getSingleSelection: function() {
		var selectedRecords = this.gdImages.getSelectionModel().getSelection();
		if( selectedRecords.length > 0) {
			return selectedRecords[0];
		}
		return null;
	},

	makeMimeTypeColumn: function() {
		return {
			dataIndex: 'file_mimetype',
			hidden: true,
			filter: {
				 type: 'string',
				 operator: 'nct',
				 value: 'image/'
			}
		};
	},

	resetFilters: function () {
		this.gdImages.filters.clearFilters(); //We disable all filters...
		this.gdImages.filters.getFilter('file_mimetype').setActive(true);//... and reenable the mime_type filter to have normal behavior
	}
});
