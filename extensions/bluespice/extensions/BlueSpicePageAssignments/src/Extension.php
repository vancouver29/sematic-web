<?php

namespace BlueSpice\PageAssignments;

use BlueSpice\Services;

class Extension extends \BlueSpice\Extension {

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		$GLOBALS['wgExtensionFunctions'][] = function() {
			array_unshift(
				$GLOBALS[ 'wgHooks' ]['userCan'], "PageAssignmentsUsersAdditionalPermissionsHooks::onUserCan"
			);
		};

		$GLOBALS["bssDefinitions"]["_PAGEASSIGN"] = array(
			"id" => "___PAGEASSIGN",
			"type" => 9,
			"show" => false,
			"msgkey" => "prefs-pageassign",
			"alias" => "prefs-pageassign",
			"label" => "Pageassign",
			"mapping" => "\\BlueSpice\\PageAssignments\\Extension::smwDataMapping"
		);
	}

	/**
	 * Callback for BlueSpiceSMWConnector that adds a semantic special property
	 * @param \SMW\SemanticData $oSemanticData
	 * @param \WikiPage $oWikiPage
	 * @param \SMW\DIProperty $oProperty
	 */
	public static function smwDataMapping( \SMW\SemanticData $oSemanticData, \WikiPage $oWikiPage, \SMW\DIProperty $oProperty ) {
		$factory = Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		if( !$target = $factory->newFromTargetTitle( $oWikiPage->getTitle() ) ) {
			return;
		}

		foreach( $target->getAssignedUserIDs() as $id ) {
			if( !$user = \User::newFromId( $id ) ) {
				continue;
			}
			$oSemanticData->addPropertyObjectValue(
				$oProperty,
				\SMWDIWikiPage::newFromTitle( $user->getUserPage() )
			);
		}
	}
}
