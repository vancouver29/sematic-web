<?php

use BlueSpice\Services;

class PageAssignmentsBookmakerHooks {

	/**
	 * Adds dependencies to SpecialBookshelfBookManager
	 * @param SpecialBookshelfBookManager $oSender
	 * @param OutputPage $oOutput
	 * @param stdClass $oConfig
	 * @return boolean
	 */
	public static function onBSBookshelfBookManager( $oSender, $oOutput, $oConfig ) {
		$oConfig->dependencies[] = 'ext.bluespice.pageassignments.bookshelfPlugin';
		return true;
	}

	/**
	 * Adds information about assignments to PDF export
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array $aParams
	 * @param DOMXPath $oDOMXPath
	 * @param array $aMeta
	 * @return boolean
	 */
	public static function onBSUEModulePDFcollectMetaData( $oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aMeta ) {
		$aMeta['assigned_users'] = '';
		$aMeta['assigned_groups'] = '';

		$aAssignedUserNames = array();
		$aAssignedGroupNames = array();

		$assignmentFactory = Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$target = $assignmentFactory->newFromTargetTitle( $oTitle );

		foreach ( $target->getAssignments() as $assignment ) {
			if( $assignment->getType() === 'user' ) {
				$aAssignedUserNames[] = $assignment->getText();
			}
			if( $assignment->getType() === 'group' ) {
				$aAssignedGroupNames[] = $assignment->getText();
			}
		}
		if( !empty( $aAssignedUserNames ) ) {
			$aMeta['assigned_users'] = implode( ', ', $aAssignedUserNames );
		}
		if( !empty( $aAssignedGroupNames ) ) {
			$aMeta['assigned_groups'] = implode( ', ', $aAssignedGroupNames );
		}

		return true;
	}

	/**
	 * Adds information about assignments to the Bookshelf BookManager grid
	 * @param Title $oBookTitle
	 * @param stdClass $oBookRow
	 * @return boolean
	 */
	public static function onBSBookshelfManagerGetBookDataRow( $oBookTitle, $oBookRow ) {
		$oBookRow->assignments = array();
		$aTexts = array();
		$assignmentFactory = Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$target = $assignmentFactory->newFromTargetTitle( $oBookTitle );

		foreach ( $target->getAssignments() as $assignment ) {
			$oBookRow->assignments[] = $assignment->toStdClass();
			$aTexts[] = $assignment->getText();
		}
		$oBookRow->flat_assignments = implode( '', $aTexts );
		return true;
	}
}