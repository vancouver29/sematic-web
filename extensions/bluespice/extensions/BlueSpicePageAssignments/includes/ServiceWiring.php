<?php

use MediaWiki\MediaWikiServices;
use BlueSpice\ExtensionAttributeBasedRegistry;

return [

	'BSPageAssignmentsAssignmentFactory' => function ( MediaWikiServices $services ) {
		$assignable = $services->getService(
			'BSPageAssignmentsAssignableFactory'
		);

		return new \BlueSpice\PageAssignments\AssignmentFactory(
			$assignable,
			$services->getLinkRenderer(),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSPageAssignmentsAssignableFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpicePageAssignmentsTypeRegistry'
		);

		return new \BlueSpice\PageAssignments\AssignableFactory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
