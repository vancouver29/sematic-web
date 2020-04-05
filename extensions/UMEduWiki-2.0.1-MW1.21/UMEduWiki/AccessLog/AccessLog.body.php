<?php
 /*
  * Copyright (c) 2011-2013 University of Macau
  *
  * Licensed under the Educational Community License, Version 2.0 (the "License");
  * you may not use this file except in compliance with the License. You may
  * obtain a copy of the License at
  *
  * http://www.osedu.org/licenses/ECL-2.0
  *
  * Unless required by applicable law or agreed to in writing,
  * software distributed under the License is distributed on an "AS IS"
  * BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
  * or implied. See the License for the specific language governing
  * permissions and limitations under the License.
  */

 /* File name:	AccessLog.body.php
  * Purpose:	Implementation of SpecialAccessLog class
  * Author:		Aleksandar Bojinovic, Peter Kin-Fong Fong
  */


class SpecialAccessLog extends SpecialPage {
	
	const DAY_IN_SECS = 86400;
	
	public function __construct() {
		parent::__construct( 'AccessLog', 'read' );
	}
	
	/**
	 * Initialize form option object with all filter options
	 */
	public function getOptions() {
		$opts = new FormOptions();
						
		$opts->add( 'user', '' );
		$opts->add( 'hideanons',  false );
		$opts->add( 'hideliu',    false );
		$opts->add( 'hidemyself', false );
		
		$opts->add( 'ns', 'all' );
		$opts->add( 'page', '' );
		$opts->add( 'pattern', false, FormOptions::BOOL );
		
		$opts->add( 'access', 'all' );
		
		$opts->add( 'days', null, FormOptions::INTNULL );
		
		$opts->add( 'limit', (int)$this->getUser()->getOption( 'rclimit' ) );
		$opts->add( 'offset', '' );
		$opts->add( 'dir', '' );
		
		return $opts;
	}
		
	/**
	 * Generate the HTML fragment for disclamar on top of the log page.
	 */
	private function getLogDisclaimer() {
		global $wgScriptPath, $wgEmergencyContact;
		
		$out  = Xml::openElement( 'td', array( 'width' => '32px' ) );
		$out .= Xml::element( 'img', array ( 'src' => $wgScriptPath .'/extensions/UMEduWiki/images/log.png' ) );
		$out .= Xml::closeElement( 'td' );
		
		$out .= Xml::openElement( 'td', array( 'bgcolor' => '#F0F0F0' ) );
		$out .=	Xml::openElement( 'p', array( 'style' => 'margin-left: 5px; font-style: italic' ) );
		$out .= $this->msg( 'accesslog-disclaimer', $wgEmergencyContact )->plain();
		$out .= Xml::closeElement( 'p' );
		$out .= Xml::closeElement( 'td' );
		
		$out  = Xml::tags( 'tr', null, $out );
		$out  = Xml::tags( 'table', array( 'border' => '0', 'cellpadding' => '0', 'cellspacing' => '0', 'width' => '100%' ), $out );
		
		return $out;
	}
	
	/**
	 * Build a drop-down box for selecting a namespace
	 * Copied from Xml class and modified to include Media and Sepcial namespaces
	 * 
	 * @param string $selected  Mixed: Namespace which should be pre-selected
	 * @param string $label     String: optional label to add to the field
	 * @return string
	 */
	public static function namespaceSelector( $selected = '', $label = null ) {
		global $wgContLang;
		$namespaces = $wgContLang->getFormattedNamespaces();
		$options = array();
		
		if( preg_match( '/^[-]?\d+$/', $selected ) ) {
			$selected = intval( $selected );
		}
		
		$namespaces = array( 'all' => wfMessage( 'namespacesall' ) ) + $namespaces;
		
		foreach( $namespaces as $index => $name ) {
			if( $index === 0 ) {
				$name = wfMessage( 'blanknamespace' );
			}
			$options[] = Xml::option( $name, $index, $index === $selected );
		}
		
		$ret = Xml::openElement( 'select', array( 'id' => 'accesslog-filter-namespace', 'name' => 'ns',
				'class' => 'namespaceselector' ) )
				. "\n" . implode( "\n", $options )
				. "\n" . Xml::closeElement( 'select' );
		if ( !is_null( $label ) ) {
			$ret = Xml::label( $label, 'ns' ) . '&#160;' . $ret;
		}
		
		return $ret;
	}
	
	/**
	 * Makes change an option link which carries all the other options
	 *
	 * @param $title Title
	 * @param $override Array: options to override
	 * @param $options Array: current options
	 * @param $active Boolean: whether to show the link in bold
	 */
	function makeOptionsLink( $title, $override, $options, $active = false ) {
		$params = $override + $options;
		
		$text = htmlspecialchars( $title );
		if ( $active ) {
			$text = '<strong>' . $text . '</strong>';
		}
		return Linker::linkKnown( $this->getTitle(), $text, array(), $params );
	}
	
	/**
	 * Generate the HTML fragment for filter forms.
	 *
	 * @param $opts Filter options obtained from user's request
	 */
	private function getLogFilterPanel( $opts ) {
		global $wgScript, $wgAccessLogAnons;
		
		$out = Html::hidden( 'title', $this->getTitle()->getPrefixedDBKey() );
		
		// Keeping show/hide settings when no user name was specified
		if ( empty( $opts['user'] ) ) {
			$out .= Html::hidden( 'hideanons',  $opts['hideanons']  );
			$out .= Html::hidden( 'hideliu',    $opts['hideliu']    );
			$out .= Html::hidden( 'hidemyself', $opts['hidemyself'] );
		}
		
		$out .= Xml::inputLabel( $this->msg( 'accesslog-filter-user' )->text(), 'user', 'accesslog-filter-user', 20, $opts['user'] );
		
		$out .= ' ' . $this->msg( 'accesslog-filter-or' )->text();
		
		// show/hide links
		$showhide = array( wfMessage( 'show' ), wfMessage( 'hide' ) );
		
		$anonfilters = array(
				'hideanons' 	=> 'accesslog-filter-user-anons',
				'hideliu'		=> 'accesslog-filter-user-loggedin',
		);
		$filters = array( 'hidemyself' => 'accesslog-filter-user-mine' );
		
		// Only display show/hide anonymous and log-in user links when 
		// user enable anonymous logging
		if ( $wgAccessLogAnons ) {
			$filters = $anonfilters + $filters;
		}
		
		$nondefault = $opts->getChangedValues();
		// Since show/hide settings are mutual exclusive with username filter,
		// remove user parameters from the show/hide link
		if ( array_key_exists( 'user', $nondefault ) ) {
			unset( $nondefault['user'] );	
		}
		
		$links = array();
		foreach ( $filters as $key => $msg ) {
			$link = $this->makeOptionsLink( $showhide[1 - $opts[$key]],
					array( $key => 1-$opts[$key] ), $nondefault );
			$links[] = wfMessage( $msg )->rawParams( $link )->escaped();
		}
		$out .= ' ';
		$out .= $this->getLang()->pipeList( $links );
		
		$out .= '<br />';
		$out .= self::namespaceSelector( $opts['ns'], $this->msg( 'namespace' )->text() );
		
		$out .= ' ';
		$out .= Xml::inputLabel( $this->msg( 'accesslog-filter-title' )->text(), 'page', 'accesslog-filter-page', 30, $opts['page'] );
		$out .= Xml::checkLabel( $this->msg( 'log-title-wildcard' )->text(), 'pattern', 'accesslog-filter-pattern', $opts['pattern'] );
		
		$out .= '<br />' . $this->msg( 'accesslog-filter-access' ) . ' ';
		$out .= Xml::openElement( 'select', array('size' => '1', 'name' => 'access' ) );
		$out .= Xml::option( $this->msg('accesslog-filter-access-all'), 'all', $opts['access'] == 'all' );
		$out .= Xml::option( $this->msg('accesslog-filter-access-view'), 'view', $opts['access'] == 'view' );
		$out .= Xml::option( $this->msg('accesslog-filter-access-edit'), 'edit', $opts['access'] == 'edit' );
		$out .= Xml::closeElement( 'select' );
		
		$out .= ' ';
		$out .= $this->msg( 'accesslog-filter-daysinput', Xml::input( 'days', 2, $opts['days'] ) )->text();
		
		$out .= ' ';
		$out .=	Xml::submitButton( 'Filter' );
		
		$out  = Xml::tags( 'form', array( 'method' => 'get', 'action' => $wgScript ), $out );
		$out  = Xml::fieldset( $this->msg('accesslog-filter-fieldset') , $out );
	
		return $out;
	}
	
	/**
	 * Generate the HTML fragment for purge old log forms.
	 */
	private function getLogPurgePanel() {
		global $wgScript;
		
		$out  = Html::hidden( 'purge', 'ask' );
		
		$out .= $this->msg( 'accesslog-purge-daysinput', Xml::input( 'pDays', 2 ) )->text();
		$out .=	Xml::submitButton( $this->msg( 'accesslog-purge-button' )->text(), array('name' => 'submit') );
		
		$out  = Xml::tags(  'form', array( 'method' => 'post', 'action' => $wgScript . '?title=' . $this->getTitle()->getPrefixedDBkey() ), $out );
		$out  = Xml::fieldset( $this->msg( 'accesslog-purge-fieldset' )->text(), $out, array( 'style' => 'border-color:#8B0000; border-style: solid;' ) );
		
		return $out;
	}
	

	/**
	 * Ask for confirmation before purging the old log entries. Return a
	 * page with a choice of "Yes" or "No".
	 *
	 * @param	$pDays	Number of days before execution time
	 */
	private function askPurge( $pDays ) {
		global $wgScriptPath, $wgScript;
	
		$out  = Xml::openElement( 'table', array( 'border' => '0', 'cellpadding' => '2', 'cellspacing' => '0' ) );
		$out .= Xml::openElement( 'tr' );
	
		$out .= Xml::openElement( 'td', array( 'valign' => 'top' ) );
		$out .= Xml::element( 'img', array ( 'src' => $wgScriptPath .'/extensions/UMEduWiki/images/sure.png' ) );
		$out .= Xml::closeElement( 'td' );
	
		$out .= Xml::openElement( 'td', array( 'valign' => 'top' ) );
		$out .= Xml::openElement( 'form', array( 'method' => 'post', 'action' => $wgScript . '?title=' . $this->getTitle()->getPrefixedDBkey() ) );
		
		$out .= '<p><b><i>';
		if ( empty( $pDays ) || $pDays < 0 ) {
			$out .= $this->msg( 'accesslog-purge-all' )->text() . '</i></b></p>';
		} else {
			$out .= $this->msg( 'accesslog-purge-days' )->numParams( $pDays )->text();
		}
		$out .= '</i></b></p>';
		
		$out .= Html::hidden( 'purge', 'confirmed' );
		$out .= Html::hidden( 'pDays', $pDays );
		
		$out .= Xml::openElement( 'p' );
		$out .=	Xml::submitButton( 'Yes', array('name' => 'submit') );
		$out .=	Xml::submitButton( 'No',  array('name' => 'submit') );
		$out .= Xml::closeElement( 'p' );
	
		$out .= Xml::closeElement( 'form' );
		$out .= Xml::closeElement( 'td' );
		$out .= Xml::closeElement( 'tr' );
		$out .= Xml::closeElement( 'table' );
	
		return $out;
	}
	
	/**
	 * Check if a user is in a designated group.
	 *
	 * @param $userGroups       Groups a user is belongs to
	 * @param $designatedGroup  The groups to check (string or array)
	 */
	private function checkUser( $userGroups, $designatedGroups ) {
		if ( ! is_array( $designatedGroups ) ) {
			$designatedGroups = array( $designatedGroups );
		}
		
		foreach ( $userGroups as $userGroup ) {
			foreach ( $designatedGroups as $designatedGroup ) {
				if ( strcmp( $userGroup, $designatedGroup ) == 0 ) {
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * Main entry point of the special page.
	 */
	function execute( $par ) {
		global $wgAccessControlPanelAllowedGroup;
		$output = $this->getOutput();
		
		$this->setHeaders();
				
		// Check user privileges
		$user = $this->getUser();
		$userGroups = $user->getGroups();
		
		$checkGroup = array( 'sysop' );
		if ( isset( $wgAccessControlPanelAllowedGroup ) ) {
			$checkGroup[] = $wgAccessControlPanelAllowedGroup;
		}
		
		if ( ! $this->checkUser( $userGroups, $checkGroup ) ) {
			$output->showErrorPage( 'badaccess', 'accesslog-badaccess' );
			return;
		}
		
		// Check if some contents was posted
		if ( $this->getRequest()->wasPosted() ) {
			$request = $this->getRequest();
			$purgeAttr = $request->getText( 'purge' );
			
			// Purging log
			if ( $purgeAttr == 'ask' ) {
				$pDays = $request->getIntOrNull( 'pDays' );
				
				$output->addHTML( $this->askPurge( $pDays ) );
				return;
		
			} else if ( $purgeAttr == 'confirmed' ) {
				$confirm = $request->getText( 'submit' );
				$pDays   = $request->getIntOrNull( 'pDays' );
				
				$dbw = wfGetDB( DB_MASTER );
		
				if ( $confirm == 'Yes' ) {
					if ( empty( $pDays ) ) {
						$dbw->delete('tw_accesslog', '*');
					} else {
						$cutoffTime = wfTimestamp( TS_MW, time() - intval( $pDays ) * self::DAY_IN_SECS );
						$dbw->delete( 'tw_accesslog', array( 'tw_log_timestamp < ' . $dbw->addQuotes( $cutoffTime ) ) );
					}
				}
				
			}
		}
		
		$opts = $this->getOptions();
		$opts->fetchValuesFromRequest( $this->getRequest() );
		
		// Always get the header first
		$output->addHTML( $this->getLogDisclaimer() );
		
		$output->addHTML( $this->getLogFilterPanel( $opts ) );
		$output->addHTML( $this->getLogPurgePanel() );
		
		$pager = new AccessLogPager( $this, $opts );
		$output->addHTML( 
				$pager->getNavigationBar() . '<table border="0" cellpadding="2" cellspacing="0">' . 
				$pager->getBody() . '</table>' . 
				$pager->getNavigationBar()
		);
	}
	
}

class AccessLogPager extends ReverseChronologicalPager {
	
	const DAY_IN_SECS = 86400;
	private $alOptions;
	
	function __construct( $accessLogPage, $opts ) {
		parent::__construct();
		$this->alOptions = $opts;
	}
	
	function getQueryInfo() {
		global $wgUser;
		
		$conds = array();
		$dbr = wfGetDB( DB_SLAVE );
		$opts = $this->alOptions->getAllValues();
		
		# It makes no sense to hide both anons and logged-in users
		# Where this occurs, force anons to be shown
		$forcebot = false;
		if( $opts['hideanons'] && $opts['hideliu'] ){
				$opts['hideanons'] = false;
		}
		
		if ( !empty( $opts['user'] ) ) {
			$conds['tw_log_username'] = $opts['user'];
		}
		else {
			if( $opts['hideliu'] ) {
				$conds[] = 'tw_log_user = 0';
			}
			if( $opts['hideanons'] ) {
				$conds[] = 'tw_log_user != 0';
			}
			
			if( $opts['hidemyself'] ) {
				if( $wgUser->getId() ) {
					$conds[] = 'tw_log_user != ' . $dbr->addQuotes( $wgUser->getId() );
				} else {
					$conds[] = 'tw_log_username != ' . $dbr->addQuotes( $wgUser->getName() );
				}
			}
		}
		
		if ( $opts['access'] != 'all' ) {
			$conds['tw_log_action'] = $opts['access'];
		}
		
		if ( $opts['ns'] != 'all' ) {
			$conds['tw_log_namespace'] = $opts['ns'];
		}
		
		if ( !empty( $opts['page'] ) ) {
			$pagetitle = Title::newFromText( $opts['page'] );
			$pagedbkey = $pagetitle->getDBkey();
			
			if( $opts['pattern'] ) {
				$conds[] = 'tw_log_title ' . $dbr->buildLike( $pagedbkey, $dbr->anyString() );
			} else {
				$conds['tw_log_title'] = $pagedbkey;
			}
		}
		
		if ( $opts['days'] !== null ) {
			$prevTs = wfTimestamp( TS_MW, time() - intval( $opts['days'] ) * self::DAY_IN_SECS );
			$conds[] = 'tw_log_timestamp >= ' . $dbr->addQuotes( $prevTs );
		}
		
		return array(
				'tables' => 'tw_accesslog',
				'fields' => '*',
				'conds'  => $conds
		);
	}
	
	function getIndexField() {
		return 'tw_log_timestamp';
	}
	
	function formatRow( $row ) {
		global $wgLang;
		
		$time   = $wgLang->timeanddate( $row->tw_log_timestamp, true );
		$action = 'accesslog-access-' . $row->tw_log_action;
		
		// Legacy support: if tw_log_user field is null, it is a version 1.x record
		if ( empty( $row->tw_log_user ) ) {
			$userId   = 0;
			$userName = $row->tw_log_username;
		} else {
			$user     = User::newFromId( $row->tw_log_user );
			$userId   = $user->getId();
			$userName = $user->getName();
		}

		// Legacy support: if tw_log_namespace field is null, it is a version 1.x record
		if ( $row->tw_log_namespace === null ) {
			$title  = Title::newFromText( $row->tw_log_title );
		} else {
			$title  = Title::newFromText( $row->tw_log_title, $row->tw_log_namespace );
		}
		
		$s = '<tr>' . 
			 '  <td><span style="color: #808080"><b>' . $time . '</b></span></td>' . 
			 '  <td>' . Linker::userLink( $userId, $userName ) . 
			            Linker::userToolLinks( $userId, $userName ) . 
			 '      <i> ' . wfMessage( $action ) . '</i> <a href="' . $title->getLocalURL() .'">' . $title->getPrefixedText() . '</a>' . 
			 '  </td>' . 
			 '</tr>';
		
		return $s;
	}
	
}

?>
