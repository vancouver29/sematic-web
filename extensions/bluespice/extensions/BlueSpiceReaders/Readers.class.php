<?php
/**
 * Readers for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpiceReaders
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Readers extension
 * @package BlueSpiceReaders
 */
class Readers extends BsExtensionMW {

	/**
	 * Hook-Handler for Hook 'ParserFirstCallInit'
	 * TODO: Make this a \Job and use better logic
	 * @param \Title $title
	 * @return boolean Always true
	 */
	public function insertTrace( \Title $title = null ) {
		$oUser = $this->getUser();
		$oTitle = $title ? $title : $this->getTitle();
		$oRevision = Revision::newFromTitle( $oTitle );

		if ( !( $oRevision instanceof Revision ) ) return true;

		$oDbw = wfGetDB( DB_MASTER );

		$oDbw->delete(
			'bs_readers',
			array(
				'readers_user_id' => $oUser->getId(),
				'readers_page_id' => $oTitle->getArticleID()
			)
		);

		$aNewRow = array();
		$aNewRow['readers_user_id'] = $oUser->getId();
		$aNewRow['readers_user_name'] = $oUser->getName();
		$aNewRow['readers_page_id'] = $oTitle->getArticleID();
		$aNewRow['readers_rev_id'] = $oRevision->getId();
		$aNewRow['readers_ts'] = wfTimestampNow();

		$oDbw->insert( 'bs_readers', $aNewRow );

		return true;
	}

	/**
	 * DEPRECATED
	 * Checks wether to set Context or not.
	 * @deprecated since version 3.0.1 - not in use anymore
	 * @return bool
	 */
	public function checkContext() {
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		$oTitle = $this->getTitle();
		$oUser = $this->getUser();

		if ( wfReadOnly() ) return false;

		if ( is_null( $oTitle ) ) return false;

		if ( !$oTitle->exists() ) return false;

		if ( $oUser->isAnon() || User::isIP( $oUser->getName() ) ) return false;

		// Do only display when user is allowed to read
		if ( !$oTitle->userCan( 'read' ) ) return false;

		// Do only display in view mode
		if ( $this->getRequest()->getVal( 'action', 'view' ) !== 'view' ) return false;

		// Do not display on SpecialPages, CategoryPages or ImagePages
		if ( in_array( $oTitle->getNamespace(), array( NS_SPECIAL, NS_CATEGORY, NS_FILE, NS_MEDIAWIKI ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the Readers segment should be added to the flyout
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function flyoutCheckPermissions( \IContextSource $context ) {
		if( $context->getTitle()->userCan( 'viewreaders' ) == false ) {
			return false;
		}
		return true;
	}

}
