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

 /*
  * File name:	AccessControlPanel.php
  * Purpose:	Controls access rights to custom defined namespaces and groups
  * Author:		Aleksandar Bojinovic, Peter Kin-Fong Fong
  */


if (!defined('MEDIAWIKI')) {
    echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['specialpage'][] = array( 
	'name' => 'Access Control Panel', 
	'author' => 'Aleksandar Bojinovic, Peter Kin-Fong Fong', 
	'description' => 'Control access rights of custom defined groups',
	'version' => '1.1.1',
	'url' => 'http://www.mediawiki.org/wiki/Extension:Access_Control_Panel'
);

$wgAutoloadClasses['AccessControlPanel'] = dirname(__FILE__) . '/AccessControlPanel.body.php';
$wgExtensionMessagesFiles['AccessControlPanel'] = dirname(__FILE__) . '/AccessControlPanel.i18n.php';
$wgSpecialPages['AccessControlPanel'] = 'AccessControlPanel';

// The user group allowed to use the control panel. Default value is 'Teacher'.
$wgAccessControlPanelAllowedGroup = 'Teacher';

// Add access control setting tables into database, if they do not exist
$wgHooks['LoadExtensionSchemaUpdates'][] = 'wfAccessControlPanelDatabaseSetup';

// Initialize access control settings
$wgExtensionFunctions[] = 'wfAccessControlPanelSetup';

/**
 * Creates tables for access control setting, if the tables do not 
 * exist. Executed when running update.php
 */
function wfAccessControlPanelDatabaseSetup( $updater = null ) {
	$dir = dirname( __FILE__ );
	
	if ( $updater === null ) {
        // <= 1.16 support
        global $wgExtNewTables;
        	
        $wgExtNewTables[] = array( 'tw_groups',
        	$dir . '/patches/AccessControlPanel.Groups.sql' );
        $wgExtNewTables[] = array( 'tw_namespaces',
        	$dir . '/patches/AccessControlPanel.Namespaces.sql' );
        $wgExtNewTables[] = array( 'tw_privileges',
        	$dir . '/patches/AccessControlPanel.Privileges.sql' );
        
	} else {
        // >= 1.17 support
        $updater->addExtensionUpdate( array( 'addTable', 'tw_groups',
        	$dir . '/patches/AccessControlPanel.Groups.sql', true ) );
        $updater->addExtensionUpdate( array( 'addTable', 'tw_namespaces',
        	$dir . '/patches/AccessControlPanel.Namespaces.sql', true ) );
        $updater->addExtensionUpdate( array( 'addTable', 'tw_privileges',
        	$dir . '/patches/AccessControlPanel.Privileges.sql', true ) );
	}
	
	return true;
}

/** 
 * Initialize access control settings.
 */
function wfAccessControlPanelSetup() {
	global	$wgGroupPermissions, 
			$wgExtraNamespaces, 
			$wgNamespacePermissionLockdown,
			$wgAccessControlPanelAllowedGroup;

	$dbr = wfGetDB( DB_SLAVE );
	
	/* Check the existence of the necessary tables */
	if ( !$dbr->tableExists('tw_groups') || 
		 !$dbr->tableExists('tw_namespaces') ||
		 !$dbr->tableExists('tw_privileges') ) {
		/* If one of table does not exist, terminate */
		return;
	}
	
	/* Selecting GROUPS */
	$myGroups = $dbr->select('tw_groups', 'tw_grp_name');

	foreach ($myGroups as $row) {
		$groupName = $row->tw_grp_name;
		$wgGroupPermissions[$groupName]['read'] = true;
	}
	

	/* Selecting NAMESPACES */
	$myNamespaces = $dbr->select('tw_namespaces', '*');
	
	foreach ($myNamespaces as $row) {
		
		$nsNumber = intval( $row->tw_ns_number );
		$nsName = $row->tw_ns_name;
		
		$wgExtraNamespaces[$nsNumber] = $nsName;
		
		/* Block read and edit actions from any user (except admin and allowed group) */
		$wgNamespacePermissionLockdown[$nsNumber]['read'] = array('sysop', $wgAccessControlPanelAllowedGroup);
		$wgNamespacePermissionLockdown[$nsNumber]['edit'] = array('sysop', $wgAccessControlPanelAllowedGroup);
	}

		
	/* Assigning PRIVILEGES */
	$myPrivileges = $dbr->select('tw_privileges', '*');
	
	foreach ($myPrivileges as $row) {
		
		$nsNumber = intval( $row->tw_ns_number );
		$privilege = $row->tw_privilege;
		$group = $row->tw_priv_group;
		
		if ( !isset($wgNamespacePermissionLockdown[$nsNumber][$privilege]) ) {
			$wgNamespacePermissionLockdown[$nsNumber][$privilege] = array( $group );
		} else {
			$wgNamespacePermissionLockdown[$nsNumber][$privilege][] = $group;
		}
		
	}
}

?>
