<?php
/**
 * SaferEdit extension for BlueSpice
 *
 * Intermediate saving of wiki edits.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for SaferEdit extension
 * @package BlueSpice_Extensions
 * @subpackage SaferEdit
 */
class SaferEdit extends BsExtensionMW {

	private $aIntermediateEditsForCurrentTitle = null;

	/**
	 * Initialization of SaferEdit extension
	 */
	protected function initExt() {

		$this->setHook( 'PageContentSaveComplete', 'clearSaferEdit' );
		$this->setHook( 'EditPage::showEditForm:initial', 'setEditSection' );
	}

	/**
	 * Clear all previously saved intermediate edits when article is saved
	 * Called by PageContentSaveComplete hook
	 * @param Article $article The article that is created.
	 * @param User $user User that saved the article.
	 * @param Content $content
	 * @param string $summary Edit summary.
	 * @param bool $minoredit Marked as minor.
	 * @param bool $watchthis Put on watchlist.
	 * @param int $sectionanchor Not in use any more.
	 * @param int $flags Bitfield.
	 * @param Revision $revision New revision object.
	 * @return bool true do let other hooked methods be executed
	 */
	public function clearSaferEdit( $article, $user, $content, $summary, $minoredit, $watchthis, $sectionanchor, $flags, $revision ) {
		$this->doClearSaferEdit( $user->getName(), $article->getTitle()->getDbKey(), $article->getTitle()->getNamespace() );
		return true;
	}

	/**
	 * Checks whether the current context is a section edit. Callback function for EditPage::showEditForm:initial hook.
	 * @param EditPage $editPage
	 * @return bool true do let other hooked methods be executed
	 */
	public function setEditSection( $editPage ) {
		$this->getOutput()->addJsConfigVars( 'bsSaferEditEditSection', $this->getRequest()->getVal( 'section', -1 ) );
		return true;
	}

	/**
	 *
	 * @param string $sText
	 * @param string $sUsername
	 * @param Title $oTitle
	 * @param integer $iSection
	 * @return boolean
	 */
	public static function saveUserEditing( $sUsername, $oTitle, $iSection = -1 ) {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		$db = wfGetDB( DB_MASTER );

		$sTable = 'bs_saferedit';
		$aFields = array(
			"se_timestamp" => wfTimestamp( TS_MW, time() )
		);
		$aConditions = array(
			"se_user_name" => $sUsername,
			"se_page_title" => $oTitle->getDBkey(),
			"se_page_namespace" => $oTitle->getNamespace(),
			"se_edit_section" => $iSection,
		);
		$aOptions = array( //needed for update reason
			'ORDER BY' => 'se_id DESC',
			'LIMIT' => 1,
		);

		if ( $oRow = $db->selectRow( $sTable, array( 'se_id' ), $aConditions, __METHOD__, $aOptions ) ) {
			$oTitle->invalidateCache();
			return $db->update(
				$sTable,
				$aFields,
				array( "se_id" => $oRow->se_id )
			);
		}

		$oTitle->invalidateCache();
		return $db->insert( $sTable, $aConditions + $aFields );
	}

	/**
	 * Actually delete all stored intermediate texts for a given user and page
	 * @param string $sUserName username of the user that edited a page
	 * @param string $sPageTitle title of the page
	 * @param int $iPageNamespace number of the namespace
	 * @return bool true do let other hooked methods be executed
	 */
	protected function doClearSaferEdit( $sUserName, $sPageTitle, $iPageNamespace ) {
		$oTitle = Title::newFromText( $sPageTitle, $iPageNamespace );
		if( empty($oTitle) ){
			return false;
		}

		$sPageTitle = str_replace( ' ', '_', $sPageTitle );
		$db = wfGetDB( DB_MASTER );
		$db->delete(
			'bs_saferedit',
			array(
				"se_user_name" => $sUserName,
				"se_page_title" => $oTitle->getDBkey(),
				"se_page_namespace" => $iPageNamespace,
			)
		);

		Title::newFromText( $sPageTitle, $iPageNamespace )->invalidateCache();
		return true;
	}
}
