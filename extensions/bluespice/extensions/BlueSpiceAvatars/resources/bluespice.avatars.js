Ext.Loader.setPath(
	'BS.Avatars',
	bs.em.paths.get( 'BlueSpiceAvatars' ) + '/resources/BS.Avatars'
);

mw.loader.using('ext.bluespice', function() {
	function showDialog() {
		Ext.require('BS.Avatars.SettingsWindow', function() {
			BS.Avatars.SettingsWindow.show();
		});
	}
	;
	$('#bs-authors-imageform').on('click', function(e) {
		e.preventDefault();
		showDialog();
		return false;
	});
});
