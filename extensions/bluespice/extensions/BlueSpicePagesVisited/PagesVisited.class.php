<?php
/**
 * PagesVisited extension for BlueSpice
 *
 * Provides a personalized list of last visited pages.
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
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage PagesVisited
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for PagesVisited extension
 * @package BlueSpice_Extensions
 * @subpackage PagesVisited
 */
class PagesVisited extends BsExtensionMW {

	/**
	 * Should cache a result list. Currently disabled.
	 * @var array
	 */
	private static $prResultListViewCache = array();
	/**
	 * Initialization of PagesVisited extension
	 */
	protected function initExt() {
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:pagesvisited';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'pagesvisited';
		$oDescriptor->desc = wfMessage( 'bs-pagesvisited-tag-pagesvisited-desc' )->escaped();
		$oDescriptor->code = '<bs:pagesvisited />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'code' => '<bs:pagesvisited count="7" maxtitlelength="40" />'
			)
		);
		$extension =  \BlueSpice\Services::getInstance()->getBSExtensionFactory()->getExtension( 'BlueSpicePagesVisited' );
		$oDescriptor->helplink = $extension->getUrl();
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	/**
	 * Hook-Handler for 'ParserFirstCallInit' (MediaWiki). Sets new Parser-Hooks for the &lt;bs:pagesvisited /&gt; and &lt;pagesvisited /&gt; tag
	 * @param Parser $oParser The current Parser object from MediaWiki Framework
	 * @return bool Always true to keep hook running.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook( 'pagesvisited', array( $this, 'onPagesVisitedTag' ) );
		$oParser->setHook( 'bs:pagesvisited', array( $this, 'onPagesVisitedTag' ) );
		return true;
	}

	/**
	 * Handles the Parser Hook for TagExtensions
	 * @param string $sInput Content of $lt;pagesvisited /&gt; from MediaWiki Framework
	 * @param array $aAttributes Attributes of &lt;pagesvisited /&gt; from MediaWiki Framework
	 * @param Parser $oParser Parser object from MediaWiki Framework
	 * @return string HTML list of recently visited pages
	 */
	public function onPagesVisitedTag( $sInput, $aAttributes, $oParser ) {
		$oParser->disableCache();
		$oParser->getOutput()->setProperty( 'bs-tag-pagesvisited', 1 );

		$oErrorListView = new ViewTagErrorList( $this );

		$iCount = BsCore::sanitizeArrayEntry( $aAttributes, 'count', 5, BsPARAMTYPE::INT );
		$iMaxTitleLength = BsCore::sanitizeArrayEntry( $aAttributes, 'maxtitlelength', 20, BsPARAMTYPE::INT );
		$sNamespaces = BsCore::sanitizeArrayEntry( $aAttributes, 'namespaces', 'all', BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING );
		$sSortOrder = BsCore::sanitizeArrayEntry( $aAttributes, 'order', 'time', BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING );

		//Validation
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array('fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 30) );
		if ( $oValidationICount->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( 'count: '.$oValidationICount->getI18N() ) );
		}

		$oValidationIMaxTitleLength = BsValidator::isValid( 'IntegerRange', $iMaxTitleLength, array('fullResponse' => true, 'lowerBoundary' => 5, 'upperBoundary' => 50) );
		if ( $oValidationIMaxTitleLength->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( 'maxtitlelength: '.$oValidationIMaxTitleLength->getI18N() ) );
		}

		if ( $oErrorListView->hasItems() ) {
			return $oErrorListView->execute();
		}
		$iCurrentNamespaceId = $oParser->getTitle()->getNamespace();
		$oListView = $this->makePagesVisitedWikiList( $iCount, $sNamespaces, $iCurrentNamespaceId, $iMaxTitleLength, $sSortOrder );
		$sOut = $oListView->execute();

		if ( $oListView instanceof ViewTagErrorList ) {
			return $sOut;
		} else {
			return \BsCore::getInstance()->parseWikiText( $sOut, $this->getTitle() );
		}
	}


	/**
	 * Gets the recently visited pages of the current user.
	 * @param int $iCount The number of pages to display
	 * @param string $sNamespaces Comma separated list of requested namespaces, i.e. "1,5,Category,101"
	 * @param int $iCurrentNamespaceId To determin wether the current namespace is in the list of requested namespaces
	 * @param string $sSortOrder Defines the sorting of the list. 'time|pagename', default is 'time'
	 * @return ViewBaseElement Contains the list in its _data member. The predefined template is '*[[{LINK}|{TITLE}]]\n'
	 */
	private function makePagesVisitedWikiList( $iCount = 5, $sNamespaces = 'all', $iCurrentNamespaceId = 0, $iMaxTitleLength = 20, $sSortOrder = 'time' ) {
		$oCurrentUser = $this->getUser();
		if ( is_null( $oCurrentUser ) ) return null; // in CLI

		//$sCacheKey = md5( $oCurrentUser->getName().$iCount.$sNamespaces.$iCurrentNamespaceId.$iMaxTitleLength );
		//if( isset( self::$prResultListViewCache[$sCacheKey] ) ) return self::$prResultListViewCache[$sCacheKey];
		$oErrorListView = new ViewTagErrorList( $this );
		$oErrorView = null;
		$aConditions = array();
		$aNamespaceIndexes = array( 0 );

		try {
			$aNamespaceIndexes = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $sNamespaces ); //Returns array of integer indexes
		} catch ( BsInvalidNamespaceException $oException ) {
			$aInvalidNamespaces = $oException->getListOfInvalidNamespaces();

			$oVisitedPagesListView = new ViewBaseElement();
			$oVisitedPagesListView->setTemplate( '<ul><li><em>{TEXT}</em></li></ul>' . "\n" );

			$iCount = count( $aInvalidNamespaces );
			$sNs = implode( ', ', $aInvalidNamespaces );
			$sErrorMsg = wfMessage( 'bs-pagesvisited-error-nsnotvalid', $iCount, $sNs )->text();

			$oVisitedPagesListView->addData( array ( 'TEXT' => $sErrorMsg ) );

			//self::$prResultListViewCache[$sCacheKey] = $oVisitedPagesListView;
			return $oVisitedPagesListView;
		}

		$aConditions = array(
			'wo_user_id' => $oCurrentUser->getId(),
			'wo_action' => 'view'
		);

		$aConditions[] = 'wo_page_namespace IN ('.implode( ',', $aNamespaceIndexes ).')'; //Add IN clause to conditions-array
		$aConditions[] = 'wo_page_namespace != -1'; // TODO RBV (24.02.11 13:54): Filter SpecialPages because there are difficulties to list them

		$aOptions = array(
			'GROUP BY' => 'wo_page_id, wo_page_namespace, wo_page_title',
			'ORDER BY' => 'MAX(wo_timestamp) DESC'
		);

		if ( $sSortOrder == 'pagename' ) $aOptions['ORDER BY'] = 'wo_page_title ASC';

		//If the page the extension is used on appears in the result set we have to fetch one row more than neccessary.
		if ( in_array( $iCurrentNamespaceId, $aNamespaceIndexes ) ) $aOptions['OFFSET'] = 1;

		$aFields = array( 'wo_page_id', 'wo_page_namespace', 'wo_page_title' );
		$sTable = 'bs_whoisonline';

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			$sTable,
			$aFields,
			$aConditions,
			__METHOD__,
			$aOptions
		);

		$oVisitedPagesListView = new ViewBaseElement();
		$oVisitedPagesListView->setTemplate( '*{WIKILINK}' . "\n" );
		$iItems = 1;
		$util = \BlueSpice\Services::getInstance()->getBSUtilityFactory();

		foreach ( $res as $row ) {
			if ( $iItems > $iCount ) break;
			$oVisitedPageTitle = Title::newFromID( $row->wo_page_id );
			/*
			// TODO RBV (24.02.11 13:52): Make SpecialPages work...
			$oVisitedPageTitle = ( $row->wo_page_namespace != NS_SPECIAL )
								? Title::newFromID( $row->wo_page_id )
								//: SpecialPage::getTitleFor( $row->wo_page_title );
								: Title::makeTitle( NS_SPECIAL, $row->wo_page_title );
			*/
			if ( $oVisitedPageTitle == null
				|| $oVisitedPageTitle->exists() === false
				|| $oVisitedPageTitle->quickUserCan( 'read' ) === false
				//|| $oVisitedPageTitle->isRedirect() //Maybe later...
				) {
				continue;
			}

			$sDisplayTitle = BsStringHelper::shorten(
				$oVisitedPageTitle->getPrefixedText(),
				array( 'max-length' => $iMaxTitleLength, 'position' => 'middle' )
			);

			$linkHelper = $util->getWikiTextLinksHelper( '' )
				->getInternalLinksHelper()->addTargets( [
				$sDisplayTitle => $oVisitedPageTitle
			] );

			$oVisitedPagesListView->addData( [
				'WIKILINK' => $linkHelper->getWikitext()
			] );
			$iItems++;
		}

		//$dbr->freeResult( $res );

		//self::$prResultListViewCache[$sCacheKey] = $oVisitedPagesListView;
		return $oVisitedPagesListView;
	}



	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:pagesvisited'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-pagesvisited'
			)
		);
		return true;
	}
}
