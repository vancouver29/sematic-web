<?php
/**
 * WatchList extension for BlueSpice
 *
 * Adds the watchlist to focus.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage WatchList
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (01.07.11 15:41)

/**
 * Base class for WatchList extension
 * @package BlueSpice_Extensions
 * @subpackage WantedArticle
 */
class WatchList extends BsExtensionMW {
	/**
	 * Initialization of WatchList extension
	 */
	protected function initExt() {
		$this->setHook( 'BeforePageDisplay' );
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
		$oDescriptor->id = 'bs:watchlist';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'watchlist';
		$oDescriptor->desc = wfMessage( 'bs-watchlist-tag-watchlist-desc' )->plain();
		$oDescriptor->code = '<bs:watchlist />';
		$oDescriptor->previewable = false;
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/WatchList';
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	 /**
	 * Registers &lt;bs:watchlist /&gt; and &lt;watchlist /&gt; tags with the MediaWiki parser
	 * @param Parser $oParser Current MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook( 'bs:watchlist', array( $this, 'onWatchlistTag' ) );
		$oParser->setHook( 'watchlist',    array( $this, 'onWatchlistTag' ) );

		return true;
	}

	/**
	 * Creates the HTML for &lt;bs:watchlist /&gt; tag
	 * @param string $sInput Inner HTML of the tag. Not used.
	 * @param array $aAttributes List of the tag's attributes.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return string Rendered HTML.
	 */
	public function onWatchlistTag( $sInput, $aAttributes, $oParser ) {
		$oParser ->getOutput()->setProperty( 'bs-tag-watchlist', 1 );

		//Get arguments
		$iCount          = BsCore::sanitizeArrayEntry( $aAttributes, 'count',          5,          BsPARAMTYPE::INT        );
		$iMaxTitleLength = BsCore::sanitizeArrayEntry( $aAttributes, 'maxtitlelength', 20,         BsPARAMTYPE::INT        );
		$sOrder          = BsCore::sanitizeArrayEntry( $aAttributes, 'order',          'pagename', BsPARAMTYPE::SQL_STRING ); //'pagename|time'

		//Validation
		$oErrorListView = new ViewTagErrorList( $this );
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array('fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 1000) );
		if ( $oValidationICount->getErrorCode() ) {
			$oErrorListView->addItem(
				new ViewTagError( 'count: '.wfMessage( $oValidationICount->getI18N() )->text() )
			);
		}

		$oValidationIMaxTitleLength = BsValidator::isValid( 'IntegerRange', $iMaxTitleLength, array('fullResponse' => true, 'lowerBoundary' => 5, 'upperBoundary' => 500) );
		if ( $oValidationIMaxTitleLength->getErrorCode() ) {
			$oErrorListView->addItem(
				new ViewTagError( 'maxtitlelength: '.wfMessage( $oValidationIMaxTitleLength->getI18N() )->text() )
			);
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$sOrder,
			array(
				'fullResponse' => true,
				'setname' => 'sort',
				'set' => array(
					'time',
					'pagename'
				)
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		if ( $oErrorListView->hasItems() ) {
			return $oErrorListView->execute();
		}

		$oWatchList = $this->fetchWatchlist( $this->getUser(), $iCount, $iMaxTitleLength, $sOrder );
		return $this->mCore->parseWikiText( $oWatchList->execute(), $this->getTitle() );
	}

	/**
	 *
	 * @param User $oCurrentUser
	 * @param int $iCount
	 * @param int $iMaxTitleLength
	 * @param string $sOrder
	 * @return ViewBaseElement
	 */
	private function fetchWatchlist( $oCurrentUser, $iCount = 10, $iMaxTitleLength = 50, $sOrder = 'pagename' ) {
		$aWatchlist = array();

		$aOptions = array();
		if( $sOrder == 'pagename' ) {
			$aOptions['ORDER BY'] = 'wl_title';
		}
		$aOptions['LIMIT'] = $iCount;

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'watchlist',
			array( 'wl_namespace', 'wl_title' ),
			array(
				'wl_user' => $oCurrentUser->getId(),
				'NOT wl_notificationtimestamp' => NULL
			),
			__METHOD__,
			$aOptions
		);

		$oWatchedArticlesListView = new ViewBaseElement();
		$oWatchedArticlesListView->setTemplate( '*{WIKILINK}' . "\n" );
		$util = \BlueSpice\Services::getInstance()->getBSUtilityFactory();
		foreach ( $res as $row ) {
			$oWatchedTitle = Title::newFromText( $row->wl_title, $row->wl_namespace );
			if( $oWatchedTitle === null
				|| $oWatchedTitle->exists() === false
				|| $oWatchedTitle->userCan( 'read' ) === false ) {
				continue;
			}
			$sDisplayTitle = BsStringHelper::shorten(
				$oWatchedTitle->getPrefixedText(),
				array( 'max-length' => $iMaxTitleLength, 'position' => 'middle' )
			);

			$linkHelper = $util->getWikiTextLinksHelper( '' )
				->getInternalLinksHelper()->addTargets( [
				$sDisplayTitle => $oWatchedTitle
			] );

			$oWatchedArticlesListView->addData( [
				'WIKILINK' => $linkHelper->getWikitext()
			] );
		}

		return $oWatchedArticlesListView;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:watchlist'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-watchlist'
			)
		);
		return true;
	}

	/**
	 * Adds CSS to Page
	 *
	 * @param OutputPage $out
	 * @param Skin       $skin
	 *
	 * @return boolean
	 */
	public function onBeforePageDisplay( &$out, &$skin ) {
		$out->addModuleStyles( 'ext.bluespice.watchlist.styles' );
		return true;
	}
}
