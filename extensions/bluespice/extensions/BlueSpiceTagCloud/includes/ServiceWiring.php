<?php

use MediaWiki\MediaWikiServices;
use BlueSpice\ExtensionAttributeBasedRegistry;

return [

	'BSTagCloudFactory' => function ( MediaWikiServices $services ) {
		$handlerRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceTagCloudStoreRegistry'
		);
		$rendererRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceTagCloudRendererRegistry'
		);
		return new \BlueSpice\TagCloud\Factory(
			$handlerRegistry,
			$rendererRegistry,
			$services->getService( 'BSRendererFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

];
