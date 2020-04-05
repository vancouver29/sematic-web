<?php

class BlueSpiceEditNotifyConnector {

	static $tableName = "bs_editnotifyconnector";

	/**
	 *
	 * @var array available action types with labels
	 */
	static $arrActions = array( //labels will not be used
		"create" => "Creation",
		"edit" => "Edit to existing pages"
	);

	/**
	 *
	 * @var string prefix
	 */
	static $prefixNamespace = "notify-namespace-selectionpage-";

	/**
	 *
	 * @var string prefix
	 */
	static $prefixCategory = "notify-category-selectionpage-";

	/**
	 *
	 * @var array
	 */
	static $categories = null;

	/**
	 * change echo notification formatter to bluespice custom version before editnotify process notifications
	 * @global array $wgExtensionFunctions
	 */
	public static function onRegistry() {
		global $wgExtensionFunctions;
		$wgExtensionFunctions[] = function() {
			global $wgHooks;
			if( !isset( $wgHooks['PageContentSave'] ) ) {
				$wgHooks['PageContentSave'] = []; // Make CI happy
			}
			array_unshift( $wgHooks['PageContentSave'], 'BlueSpiceEditNotifyConnector::onPageContentSave' );
			$wgHooks[ 'BeforeCreateEchoEvent' ][] = "BlueSpiceEditNotifyConnector::onBeforeCreateEchoEvent";
		};
		global $wgEditNotifyAlerts;
		$wgEditNotifyAlerts = [];
	}

	/**
	 * register notifications for editnotify, should be included / started before same hook call of editnotify
	 * @param array $echoNotifications
	 * @param array $echoNotificationCategories
	 */
	public static function onBeforeCreateEchoEvent( &$echoNotifications, $echoNotificationCategories ) {
		foreach ( $echoNotifications as $key => $val ) {
			if ( preg_match( '#^edit-notify#', $key ) === 1 ) {
				$val[ 'formatter-class' ] = "BsNotificationsFormatter";
				$echoNotifications[ $key ] = $val;
			}
		}
		return true;
	}

	/**
	 *
	 * @return array converted column options from $arrActions for checkmatrix
	 */
	public static function getColumns() {
		$arrColumns = array();
		foreach ( self::$arrActions as $sKey => $oVal ) {
			$arrColumns[ wfMessage( $sKey )->text() ] = "page-" . $sKey;
		}
		return $arrColumns;
	}

	/**
	 * Hooks/GetPreferences
	 *
	 * make user options available to set namespace and category notifications
	 *
	 * https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 *
	 * @param User $user
	 * @param array $preferences
	 * @return boolean
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {

		$requestContext = RequestContext::getMain();
		$contLang = $requestContext->getLanguage();

		//Add namespace selection matrix
		$arrNamespaces = array();

		foreach ( $contLang->getFormattedNamespaces() as $ns => $title ) {
			if ( $ns > 0 ) {
				$arrNamespaces[ $title ] = $ns;
			} elseif ( $ns == 0 ) {
				$arrNamespaces[ 'Main' ] = $ns;
			}
		}

		$preferences[ 'notify-namespace-selection' ] = array(
			'type' => 'checkmatrix',
			'section' => 'echo/namespace-notifications',
			'help-message' => 'tog-help-notify-namespace-selection', // a system message (optional)
			'rows' => $arrNamespaces,
			'columns' => self::getColumns()
		);

		$cats = static::getAvailableCategories();
		if( count( $cats ) < 1 ) {
			return true;
		}
		$rows = [];
		foreach( $cats as $cat ) {
			$rows[str_replace( '_', ' ', $cat )] = $cat;
		}
		$preferences[ 'notify-category-selection' ] = [
			'type' => 'checkmatrix',
			'section' => 'echo/category-notifications',
			'rows' => $rows,
			'columns' => static::getColumns(),
		];

		return true;
	}

	public static function getAvailableCategories() {
		if( static::$categories ) {
			return static::$categories;
		}
		$res = wfGetDB( DB_REPLICA )->select(
			'category',
			'cat_title',
			[],
			__METHOD__
		);
		static::$categories = [];
		foreach( $res as $row ) {
			static::$categories[] = $row->cat_title;
		}
		return static::$categories;
	}

	/**
	 * save namespace notification settings in separate table
	 * @param User $user
	 * @param array $options
	 * @return boolean handover to next call
	 */
	public static function onUserSaveOptions( User $user, array &$options ) {
		$dbw = wfGetDB( DB_MASTER );
		//remove all namespace settings for current user from editnotifyconnector table
		$dbw->delete( self::$tableName, array( "enc_username" => $user->getName() ) );
		//set editnotifyconnector entries for current user

		foreach( $options as $key => $value ) {
			if ( strpos( $key, 'notify-namespace-selection' ) !== 0 ) {
				continue;
			}
			if ( !$value ) {
				continue;
			}
			$arrKey = explode( "-", $key );
			$action = $arrKey[ 3 ];
			$nsId = $arrKey[ 4 ];
			$dbw->insert(
				self::$tableName,
				array(
					"enc_ns_id" => $nsId,
					"enc_username" => $user->getName(),
					"enc_action" => $action
				)
			);
		}

		return true;
	}

	/**
	 * append content on global settings "$wgEditNotifyAlerts" for editnotify
	 * from database for namespace of current page, lookup user settings with maching
	 * namespace notification settings
	 *
	 * @global type $wgEditNotifyAlerts
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content $content
	 * @param string $summary
	 * @param boolean $isMinor
	 * @param boolean $isWatch
	 * @param int $section
	 * @param int $flags
	 * @param Revision $revision
	 * @param Status $status
	 * @param int $baseRevId
	 * @param int $undidRevId
	 * @return boolean
	 */
	public static function onPageContentSave( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $status ) {
		$requestContext = RequestContext::getMain();
		$contLang = $requestContext->getLanguage();

		global $wgEditNotifyAlerts;

		$wgEditNotifyAlerts = array();

		$nsID = $wikiPage->getTitle()->getNamespace();
		$arrNamespaces = $contLang->getFormattedNamespaces();
		$nsTitle = $arrNamespaces[ $nsID ];
		$dbr = wfGetDB( DB_SLAVE );

		//set config for namespaces
		foreach ( self::$arrActions as $oKey => $oVal ) {
			$arrNSNotifyConfig = array();
			$arrNSNotifyConfig[ "namespace" ] = $nsTitle;
			$arrNSNotifyConfig[ "action" ] = $oKey;
			//This crashes, because DynamicPageList creates a page within the
			//first installation before this table even exists!
			try {
				//get users with abo for current namespace and action combination
				$res = $dbr->select(
				  array( self::$tableName ),
				  array( 'enc_username' ),
				  array( 'enc_ns_id' => $nsID, 'enc_action' => $oKey )
				);
				$arrUser = array();
				foreach ( $res as $row ) {
					$arrUser[] = $row->enc_username;
				}
				$arrNSNotifyConfig[ "users" ] = $arrUser;

				$wgEditNotifyAlerts[] = $arrNSNotifyConfig;
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}
		}

		//$wikiPage->getCategories() always returns 0 categories at this
		//point...


		$title = $wikiPage->getTitle();
		$text = ContentHandler::getContentText( $content );
		$content = ContentHandler::makeContent( $text, $title );

		if( !$content instanceof WikitextContent ) {
			return true;
		}

		$cats = $content->getParserOutput( $title )->getCategoryLinks();

		foreach( $cats as $cat ) {
			foreach ( self::$arrActions as $key => $val ) {
				$notifyConfig = [
					'action' => $key,
					'category' => str_replace( '_', ' ', $cat ),
					'users' => [],
				];

				//This crashes, because DynamicPageList creates a page within the
				//first installation before this table even exists!
				try {
					$res = $dbr->select(
						['user_properties'],
						['up_user'],
						['up_property' => "notify-category-selectionpage-$key-$cat"],
						__METHOD__
					);

					if( !$res || $res->numRows() < 1 ) {
						continue;
					}
					foreach( $res as $row ) {
						$notifyConfig['users'][] = User::newFromId(
							$row->up_user
						)->getName();
					}

					$wgEditNotifyAlerts[] = $notifyConfig;
				} catch ( Exception $e ) {
					error_log( $e->getMessage() );
				}
			}
		}
		return true;
	}

	/**
	 *
	 * @param DatabaseUpdater $updater
	 * @return boolean
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( self::$tableName, __DIR__ . "/../db/editnotifyconnector.sql" );
		return true;
	}

}
