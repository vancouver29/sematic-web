mw.loader.using( [ 'schema.Edit', 'ext.eventLogging.subscriber' ] ).then( function () {
	var M = mw.mobileFrontend,
		// Schema provided by ext.eventLogging.subscriber class
		Schema = mw.eventLog.Schema, // resource-modules-disable-line
		user = M.require( 'mobile.startup/user' ),
		/**
		 * Edit schema
		 * https://meta.wikimedia.org/wiki/Schema:Edit
		 */
		schemaEdit = new Schema(
			'Edit',
			mw.config.get( 'wgWMESchemaEditSampleRate' ),
			{
				'page.id': mw.config.get( 'wgArticleId' ),
				'page.revid': mw.config.get( 'wgRevisionId' ),
				'page.title': mw.config.get( 'wgPageName' ),
				'page.ns': mw.config.get( 'wgNamespaceNumber' ),
				'user.id': user.getId(),
				'user.class': user.isAnon() ? 'IP' : undefined,
				'user.editCount': mw.config.get( 'wgUserEditCount', 0 ),
				'mediawiki.version': mw.config.get( 'wgVersion' ),
				platform: 'phone',
				integration: 'page',
				version: 1
			}
		);

	mw.trackSubscribe( 'mf.schemaEdit', function ( topic, data ) {
		data['action.' + data.action + '.type'] = data.type;
		delete data.type;
		data['action.' + data.action + '.mechanism'] = data.mechanism;
		delete data.mechanism;
		// data['action.' + data.action + '.timing'] = Math.round( computeDuration( ... ) );
		data['action.' + data.action + '.message'] = data.message;
		delete data.message;

		schemaEdit.log( data );
	} );

} );
