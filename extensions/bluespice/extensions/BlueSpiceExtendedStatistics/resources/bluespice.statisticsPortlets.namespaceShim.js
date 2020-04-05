//This dependency is intentionally not set on the RL definition, to makt sure
//the script is loaded pretty early
//Also load 'ext.extjsbase.charts'
mw.loader.using( [ 'ext.bluespice.extjs', 'ext.extjsbase.charts' ] ).done( function() {
	Ext.Loader.setPath(
		'BS.Statistics',
		bs.em.paths.get('BlueSpiceExtendedStatistics') + '/resources/BS.Statistics'
	);
} );