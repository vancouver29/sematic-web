<?php

/**
 * WhoIsOnline extension for BlueSpice
 *
 * Displays a list of users who are currently online.
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
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource

/**
 * Base class for WhoIsOnline extension
 * @package BlueSpice_Extensions
 * @subpackage WhoIsOnline
 */
class WhoIsOnline extends BsExtensionMW {

	private $aWhoIsOnlineData = array();
	protected static $bContextAlreadyTraced = false;



	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		// Hooks
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BeforePageDisplay');
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BsAdapterAjaxPingResult' );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( !$this->getTitle()->userCan( 'read' ) ) return true;

		$oOutputPage->addModules( 'ext.bluespice.whoisonline' );
		return true;
	}

	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if ( $type != 'tags' ) return true;

		$extension = \BlueSpice\Services::getInstance()->getBSExtensionFactory()
				->getExtension( 'BlueSpiceWhoIsOnline' );
		$helplink = $extension->getUrl();

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:whoisonlinecount';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'whoisonlinecount';
		$oDescriptor->desc = wfMessage( 'bs-whoisonline-tag-whoisonlinecount-desc' )->plain();
		$oDescriptor->code = '<bs:whoisonlinecount />';
		$oDescriptor->previewable = false;
		$oDescriptor->helplink = $helplink;
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:whoisonlinepopup';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'whoisonlinepopup';
		$oDescriptor->desc = wfMessage( 'bs-whoisonline-tag-whoisonlinepopup-desc' )->plain();
		$oDescriptor->code = '<bs:whoisonlinepopup />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'code' => '<bs:whoisonlinepopup anchortext="Online users" />'
			)
		);
		$oDescriptor->helplink = $helplink;
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	/**
	 * Add various tags and magic words. Magic Words are only supported for legacy reasons.
	 * @param Parser $oParser Current MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		$oTitle = RequestContext::getMain()->getTitle();

		//Only trace once, or the bs_whoisonline table gets filled with all
		//transcluded articles f.e.
		if( !static::$bContextAlreadyTraced && $oTitle instanceof Title ) {
			$this->insertTrace(
				$oTitle,
				RequestContext::getMain()->getUser(),
				RequestContext::getMain()->getRequest()
			);
			static::$bContextAlreadyTraced = true;
		}

		$oParser->setFunctionHook( 'userscount', array( $this, 'onUsersCount' ) );
		$oParser->setHook( 'bs:whoisonline:count', array( $this, 'onUsersCountTag' ) );
		$oParser->setHook( 'bs:whoisonlinecount', array( $this, 'onUsersCountTag' ) );
		$oParser->setFunctionHook( 'userslink', array( $this, 'onUsersLink' ) );
		$oParser->setHook( 'bs:whoisonline:popup', array( $this, 'onUsersLinkTag' ) );
		$oParser->setHook( 'bs:whoisonlinepopup', array( $this, 'onUsersLinkTag' ) );

		return true;
	}

	/**
	 * Fetches the HTML for bs:whoisonline:count tag
	 * @param string $sInput Inner HTML of the tag. Not used.
	 * @param array $aAttributes List of the tag's attributes.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return string Rendered HTML.
	 */
	public function onUsersCountTag( $sInput, $aAttributes, $oParser ) {
		$aOut = $this->onUsersCount( $oParser );
		return $aOut[0];
	}

	/**
	 * Renders bs:whoisonline:count output.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return array Rendered HTML and flags. Used by magic word function hook as well as by onUsersCountTag.
	 */
	public function onUsersCount( $oParser ) {
		$oParser ->getOutput()->setProperty( 'bs-tag-userscount', 1 );
		$sOut = '<span class="bs-whoisonline-count">'.count( $this->getWhoIsOnline() ).'</span>';

		return array( $sOut, 'noparse' => 1 );
	}

	/**
	 * Fetches the HTML for bs:whoisonline:popup tag
	 * @param string $sInput Inner HTML of the tag. Not used.
	 * @param array $aAttributes List of the tag's attributes.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return string Rendered HTML.
	 */
	public function onUsersLinkTag( $sInput, $aAttributes, $oParser ) {
		//Validation in onUsersLink.
		return $this->onUsersLink( $oParser, isset($aAttributes[ 'anchortext' ])?$aAttributes[ 'anchortext' ] : "" );
	}

	/**
	 * Renders bs:whoisonline:popup output.
	 * @param Parser $oParser MediaWiki parser object.
	 * @param string $sLinkTitle Label of the link that is the anchor of the flyout
	 * @return array Rendered HTML and flags. Used by magic word function hook as well as by onUsersLinkTag.
	 */
	public function onUsersLink( $oParser, $sLinkTitle = '' ) {
		$oParser->disableCache();
		$oParser ->getOutput()->setProperty( 'bs-tag-userslink', 1 );

		$sLinkTitle = BsCore::sanitize( $sLinkTitle, '', BsPARAMTYPE::STRING );

		if( empty( $sLinkTitle ) ) $sLinkTitle = wfMessage('bs-whoisonline-widget-title')->plain();
		$oWhoIsOnlineTagView = new ViewWhoIsOnlineTag();
		$oWhoIsOnlineTagView->setOption( 'title', $sLinkTitle );
		$oWhoIsOnlineTagView->setPortlet( $this->getPortlet() );
		$sOut = $oWhoIsOnlineTagView->execute();

		return $oParser->insertStripItem( $sOut, $oParser->mStripState );
	}

	/**
	 * Renders the inner part of tag and widget view.
	 * @param mixed $vWrapperId Distinct ID. Used if several instances are used on a page.
	 * @return string Rendered HTML
	 */
	private function getPortlet( $vWrapperId = false, $iLimit = 0 ) {
		$aWhoIsOnline = $this->getWhoIsOnline();

		// who (names)
		$oWhoIsOnlineWidgetView = new ViewWhoIsOnlineWidget();
		$oWhoIsOnlineWidgetView->setOption( 'count', count($aWhoIsOnline) );
		$oWhoIsOnlineWidgetView->setOption( 'wrapper-id', $vWrapperId );

		$iCount = 1;
		foreach( $aWhoIsOnline as $oWhoIsOnline) {
			if( $iLimit > 0 && $iCount > $iLimit ) break;

			$oUser = User::newFromId( $oWhoIsOnline->wo_user_id );
			$userHelper = \BlueSpice\Services::getInstance()
				->getBSUtilityFactory()->getUserHelper( $oUser );
			$oWhoIsOnlineItemWidgetView = new ViewWhoIsOnlineItemWidget();
			$oWhoIsOnlineItemWidgetView->setUser( $oUser );
			$oWhoIsOnlineItemWidgetView->setUserDisplayName(
				$userHelper->getDisplayName()
			);
			$oWhoIsOnlineWidgetView->addItem( $oWhoIsOnlineItemWidgetView );
			$iCount++;
		}

		return $oWhoIsOnlineWidgetView;
	}

	/**
	 * Hook-Handler for BS hook BsAdapterAjaxPingResult
	 * @global User $wgUser
	 * @global WebRequest $wgRequest
	 * @param string $sRef
	 * @param array $aData
	 * @param integer $iArticleId
	 * @param array $aSingleResult
	 * @return boolean
	 */
	public function onBsAdapterAjaxPingResult( $sRef, $aData, $iArticleId, $sTitle, $iNamespace, $iRevision, &$aSingleResult ) {
		if ( $sRef !== 'WhoIsOnline') return true;

		$oTitle = Title::newFromText( $sTitle, $iNamespace );
		if ( is_null($oTitle) || !$oTitle->userCan('read') ) return true;

		$aWhoIsOnline = $this->getWhoIsOnline();
		$aSingleResult['count'] = count( $aWhoIsOnline );

		$aSingleResult['portletItems'] = array();
		foreach ( $aWhoIsOnline as $oWhoIsOnline ) {
			$oUser = User::newFromId( $oWhoIsOnline->wo_user_id );
			$userHelper = \BlueSpice\Services::getInstance()
				->getBSUtilityFactory()->getUserHelper( $oUser );
			$oWhoIsOnlineItemWidgetView = new ViewWhoIsOnlineItemWidget();
			$oWhoIsOnlineItemWidgetView->setUser( $oUser );
			$oWhoIsOnlineItemWidgetView->setUserDisplayName(
				$userHelper->getDisplayName()
			);
			$aSingleResult['portletItems'][] = $oWhoIsOnlineItemWidgetView->execute();
		}

		$aSingleResult['success'] = true;
		return true;
	}

	/**
	 * Loads WhoIsOnline data from DB
	 * @param string $sOrderBy
	 * @param bool $bForceReload
	 * @return type
	 */
	private function getWhoIsOnline( $sOrderBy = '', $bForceReload = false){
		if ( isset( $this->aWhoIsOnlineData[$sOrderBy] ) && $bForceReload === false ) {
			return $this->aWhoIsOnlineData[$sOrderBy];
		}

		if ( empty( $sOrderBy ) ) {
			$sOrderBy = $this->getUser()->getOption(
				'bs-whoisonline-pref-orderby'
			);
		}

		$sMaxIdle = $this->getConfig()->get( 'WhoIsOnlineMaxIdleTime' );

		$this->aWhoIsOnlineData[$sOrderBy] = array();

		$aTables = array(
			'bs_whoisonline'
		);
		$aFields = array(
			'wo_user_id', 'wo_user_name'
		);
		$aConditions = array(
			'wo_timestamp > '.( time() - $sMaxIdle )
		);
		$aOptions = array(
			'GROUP BY' => 'wo_user_name',
			//'LIMIT'    => (int) $iLimit,
		);

		$dbr = wfGetDB( DB_REPLICA );
		switch ( $sOrderBy ) {
			case 'name' :
			default :
				$aOptions['ORDER_BY'] = 'wo_user_name ASC';
			case 'onlinetime' :
				$aOptions['ORDER_BY'] = 'MAX(wo_timestamp) DESC';
		}

		$rRes = $dbr->select( $aTables, $aFields, $aConditions, __METHOD__, $aOptions );
		while( $oRow = $dbr->fetchObject($rRes) )
			$this->aWhoIsOnlineData[$sOrderBy][] = $oRow;

		return $this->aWhoIsOnlineData[$sOrderBy];
	}

	/**
	 * Inserts a trace of the user action into the database
	 * @param Title $oTitle
	 * @param User $oUser
	 * @param Request $oRequest
	 * @return boolean
	 */
	public function insertTrace( $oTitle, $oUser, $oRequest) {
		if ( wfReadOnly() ) return true;
		if ( ( $oUser->getId() == 0 ) ) return true; // Anonymous user

		$sPageTitle = $oTitle->getText();
		if ( $sPageTitle == '-' ) return true; // otherwise strange '-' with page_id 0 are logged

		$iPageId             = $oTitle->getArticleId();
		$iPageNamespaceId    = $oTitle->getNamespace();
		$iCurrentTimestamp   = time();
		$vLastLoggedPageHash = $oRequest->getSessionData( $this->mExtensionKey.'::lastLoggedPageHash' );
		$vLastLoggedTime     = $oRequest->getSessionData( $this->mExtensionKey.'::lastLoggedTime' );
		$sCurrentPageHash    = md5( $iPageId.$iPageNamespaceId.$sPageTitle ); //this combination should be pretty unique, even with specialpages.
		$iMaxIdleTime = $this->getConfig()->get( 'WhoIsOnlineMaxIdleTime' );
		$iInterval = $this->getConfig()->get( 'WhoIsOnlineInterval' );

		if ( $vLastLoggedPageHash == $sCurrentPageHash
			&& $vLastLoggedTime + $iMaxIdleTime + $iInterval + ($iMaxIdleTime * 0.1) > $iCurrentTimestamp )
				return true;

		//log action
		$oRequest->setSessionData( $this->mExtensionKey.'::lastLoggedPageHash', $sCurrentPageHash );
		$oRequest->setSessionData( $this->mExtensionKey.'::lastLoggedTime', $iCurrentTimestamp );

		$iRemoveEntriesAfter = 2592000;

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'bs_whoisonline',
			array( 'wo_timestamp < ' . ( $iCurrentTimestamp - $iRemoveEntriesAfter ) )
		);

		$aNewRow = array();

		$aNewRow[ 'wo_page_id' ]        = $oTitle->getArticleId();
		$aNewRow[ 'wo_page_namespace' ] = $oTitle->getNamespace();
		$aNewRow[ 'wo_page_title' ]     = $sPageTitle;
		$aNewRow[ 'wo_user_id' ]        = $oUser->getId();
		$aNewRow[ 'wo_user_name' ]      = $oUser->getName();
		$aNewRow[ 'wo_user_real_name' ] = $oUser->getRealName();
		$aNewRow[ 'wo_timestamp' ]      = $iCurrentTimestamp;
		$aNewRow[ 'wo_action' ]         = $oRequest->getVal( 'action', 'view' );

		$dbw->insert( 'bs_whoisonline', $aNewRow );

		return true;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:whoisonline:count'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-userscount'
			)
		);
		$aCollectorsConfig['bs:whoisonline:popup'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-userslink'
			)
		);
		return true;
	}
}
