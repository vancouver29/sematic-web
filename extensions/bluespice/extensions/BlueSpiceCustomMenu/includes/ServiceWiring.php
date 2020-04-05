<?php

use MediaWiki\MediaWikiServices;
use BlueSpice\ExtensionAttributeBasedRegistry;

return [

	'BSCustomMenuFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\CustomMenu\Factory(
			new ExtensionAttributeBasedRegistry( 'BlueSpiceCustomMenuRegistry' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

];
