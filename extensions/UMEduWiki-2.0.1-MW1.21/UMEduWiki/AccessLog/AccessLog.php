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

 /* File name:	AccessLog.php
  *	Purpose:	Logs user access to MediaWiki
  * Author:		Aleksandar Bojinovic, Peter Kin-Fong Fong
  */

if (!defined('MEDIAWIKI')) {
    echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['specialpage'][] = array( 
	'name' => 'Access Log', 
	'author' => 'Aleksandar Bojinovic, Peter Kin-Fong Fong', 
	'url' => 'http://www.mediawiki.org/wiki/Extension:Access_Log', 
	'description' => 'Logs user access to MediaWiki', 
	'version' => '2.0'
);

// Anonymous logging switch. Setting its value to true to enable anonymous logging.
$wgAccessLogAnons = false;

$wgAutoloadClasses['SpecialAccessLog'] = dirname(__FILE__) . '/AccessLog.body.php';
$wgExtensionMessagesFiles['AccessLog'] = dirname(__FILE__) . '/AccessLog.i18n.php';
$wgSpecialPages['AccessLog'] = 'SpecialAccessLog';

// Hooks to log user actions
$wgHooks['BeforePageDisplay'][] = 'accessLogViewAction';
$wgHooks['ArticleSaveComplete'][] = 'accessLogEditAction';

// Add access log table into database if not exist
$wgHooks['LoadExtensionSchemaUpdates'][] = 'accessLogDatabaseSetup';

/**
 * Shorthand operation to insert log entry into table.
 * 
 * @param  $userid    User ID of the action performer
 * @param  $username  Username of the action performer
 * @param  $namespace Namespace ID of the article
 * @param  $title     Title of the article
 * @param  $action    Action performed (read or edit)
 */
function insertLog( $userid, $username, $namespace, $title, $action ) {
	
	$log_entries_arr = array( 
		'tw_log_user'		=> $userid, 
		'tw_log_username'	=> $username, 
		'tw_log_timestamp'	=> wfTimestamp( TS_MW ), 
		'tw_log_namespace'	=> $namespace, 
		'tw_log_title'		=> $title, 
		'tw_log_action'		=> $action 
	);
	
	$dbw = wfGetDB( DB_MASTER );
	$dbw->begin();
	$dbw->insert( 'tw_accesslog', $log_entries_arr );
	$dbw->commit();
	
}

/**
 * Log the page view action into log table.
 */
function accessLogViewAction( &$out, &$sk ) {
	global $wgUser, $wgTitle, $wgRequest, $wgAccessLogAnons;
	
	$user = $wgUser;
	$title = $wgTitle;
	
	if ( $user->isLoggedIn() || $wgAccessLogAnons ) {
		$action = $wgRequest->getText( 'action' );
		
		// Inserting 'read' (aka 'view') action
		if ( $action == '' || $action == 'view' ) {
			insertLog( $user->getId(), $user->getName(), 
					   $title->getNamespace(), $title->getDBKey(), 'view' );
		}
	}
	
	return true;
} 
 
/**
 * Log the page edit action into log table.
 */
function accessLogEditAction( &$article, &$user, $text, $summary,
 $minoredit, $watchthis, $sectionanchor, &$flags, $revision, 
 &$status, $baseRevId, &$redirect ) {
 	
	if ( $user->isLoggedIn() || $wgAccessLogAnons ) {
		$title = $article->getTitle();
		
		// Inserting 'edit' action
		insertLog( $user->getId(), $user->getName(), 
				   $title->getNamespace(), $title->getDBKey(), 'edit' );
	}
	
	return true;
}

/**
 * Create access log table, if it does not exist. Update access log table, 
 * if its schema is version 1.x format.
 * Execute within update.php
 */
function accessLogDatabaseSetup( DatabaseUpdater $updater ) {
	// Create table if it does not exist
	$updater->addExtensionTable( 'tw_accesslog', dirname( __FILE__ ) . '/AccessLog.sql' );
	
	// Add user ID and namespace fields if they do not exist
	// (Introduced in version 2.0)
	$updater->addExtensionUpdate( array( 'addField', 'tw_accesslog', 'tw_log_user', dirname( __FILE__ ) . '/AccessLog.patch.user.sql', true ) );
	$updater->addExtensionUpdate( array( 'addField', 'tw_accesslog', 'tw_log_namespace', dirname( __FILE__ ) . '/AccessLog.patch.namespace.sql', true ) );
	
	return true;
}

?>
