Ext.define( 'BS.Calumma.dialog.NewPage', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.form.field.TitleCombo' ],
	title: mw.message( 'bs-action-new-page-title' ).plain(),

	namespace: '',

	makeItems: function() {
		this.cbPageName = Ext.create( 'BS.form.field.TitleCombo', {
			fieldLabel: mw.message('bs-action-new-page-text').plain(),
			enableKeyEvents: true,
			value: this.namespace,
			listeners: {
				focusleave: function ( comboBox ) {
					this.cbPageName.select( document.getElementById( this.cbPageName.id + '-inputEl' ).value );
				}
			}
		} );
		this.cbPageName.on( 'keypress', this.onPageNameKeypress, this );
		return [
			this.cbPageName
		];
	},
	onPageNameKeypress: function ( combo, e, eOpts ) {
		if ( e.charCode === 13 ) {
			this.cbPageName.select( document.getElementById( this.cbPageName.id + '-inputEl' ).value );
			this.onBtnOKClick();
		}
	},
	getData: function () {
		return this.cbPageName.getRawValue();
	},
	show: function() {
		this.callParent(arguments);
		this.cbPageName.focus();
	}
} );
