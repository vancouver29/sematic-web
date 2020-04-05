// I tried hard with toolFactory registration, but
// eventually gave up. This is a hacky but working
// solution.
mw.hook( 've.activationComplete' ).add( function () {
	if ( ve.init.target.getActions().bsCancelAdded ) {
		return;
	};

	var cancelButton = new OO.ui.ButtonWidget(
			{
				label: '',
				icon: 'clear',
				iconTitle: mw.msg( 'bs-visualeditorconnector-cancel-edit' ),
				classes: [ 'bs-visualeditorconnector-cancel-edit' ],
				framed: false,
				disabled: false,
				active: true
			}
	);

	cancelButton.on( 'click', function () {
			window.location.href = mw.util.getUrl();
	});

	cancelButton.destroy = function() {};

	ve.init.target.getActions().insertItem( cancelButton );

	ve.init.target.getActions().bsCancelAdded = true;
});
