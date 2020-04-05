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

 /* File name:	AccessControlPanel.body.php
  * Purpose:	Controls access rights to custom defined namespaces and groups
  * Author:		Aleksandar Bojinovic, Peter Kin-Fong Fong
  */

class AccessControlPanel extends SpecialPage {

	function AccessControlPanel() {
		SpecialPage::SpecialPage( 'AccessControlPanel', 'protect' );
	}

	/**
	 * Returns true if the request contains POST data
	 */
	private function isPosted() {
		if (isset($_POST['submit']) || 
			isset($_POST['submitMembers']) || 
			isset($_POST['submitAdd']) || 
			isset($_POST['submitRemove']) )
			return true;
		
		return false;
	}

	/**
	 * Check if a user is in a designated group.
	 * 
	 * @param $userGroups		Groups a user is belongs to
	 * @param $designatedGroup	The group to check
	 */
	private function checkUser($userGroups, $designatedGroup) {
		for ($i=0; $i<count($userGroups); $i++) {
			if (strcmp($userGroups[$i], $designatedGroup) == 0) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Replace space with underscore
	 */
	private function removeSpaces($str) {
		return str_replace(" ", "_", $str);
	}

	/**
	 * Create a wrapper object to database record. Add an additional 
	 * field to record the state of row. Used in addUser to remove user
	 * from non-selected group
	 * 
	 * @param $result	ResultWrapper object from Mediawiki
	 * @param $value	The column selected
	 * @return A new array with a flag and a selected column
	 */
	private function createWrapper($result, $value) {
			
		$output = array();
			
		for ($i = 0; $i < $result->numRows(); $i++) {
			$row = $result->fetchRow();
			$name = $row[$value];
			array_push($output, array(0, $name));
		}
			
		return $output;
	}


	/**
	 * Update flags of record wrapper object.
	 * 
	 * @param $wrapper	The record wrapper object
	 * @param $gName	Group name to be flagged
	 */
	private function updateFlags(&$wrapper, $gName) {
		for ($i=0; $i<count($wrapper); $i++) {
			if (strcmp($wrapper[$i][1], $gName) == 0) {
				$wrapper[$i][0] = 1;
				return;
			}
		}
		return;
	}

	/**
	 * Generate an array from GET-format string value.
	 */
	private function getArrayFromValues($groupsAvailable) {
		$arr = explode("#", $groupsAvailable);
		return $arr;
	}

	/**
	 * Add a new group in wiki. Also add a new namespace and an associated talk namespace.
	 * 
	 * @param	string	$groupName	Name of the new group
	 * @return	true if success, or a string stating the cause of failure
	 */
	private function addGroup( $groupName ) {
		
		$dbw = wfGetDB( DB_MASTER );

		# Check if group name already exists in database
		$result = $dbw->select('tw_groups', '*', array('tw_grp_name' => $groupName));
			
		# If there is already a group with the same name, make error
		if ($result->numRows() > 0) {
			return "Group name '$groupName' already exists.";
		}
			
		# Then I check if the name is other than null
		if ($groupName != '') {
			# First I change spaces to underscores
			$groupName = $this->removeSpaces($groupName);

			# And then I enter it to database
			if ( !( $dbw->insert('tw_groups', array('tw_grp_name' => $groupName) ) ) ) {
				return "Failed to insert the new group '$groupName' into database.";
			}
		} else {
			return "Group name must not be empty.";
		}
			
		# Then I insert the namespace name
		# As for now, group name = namespace name
			
		$result = $dbw->selectRow('tw_namespaces', array('MAX(tw_ns_number) AS maxnsnum'));
			
		# First, get the new highest namespace number
		# (custom namespace should have a number >= 100)
		$ns_number = intval($result->maxnsnum) + 1;
		$ns_number = max(100, $ns_number);
		# And its discussion number
		$ns_talk_number = $ns_number + 1;
		
		# Inserting the namespace
		if ( !( $dbw->insert('tw_namespaces', array('tw_ns_number' => $ns_number, 'tw_ns_name' => $groupName)) ) ) {
			return "Failed to add new namespace '$groupName' into database.";
		}
			
		# Inserting its discussion page
		if ( !( $dbw->insert('tw_namespaces', array('tw_ns_number' => $ns_talk_number, 'tw_ns_name' => $groupName."_talk")) ) ) {
			return "Failed to add new namespace '$groupName" . "_talk' into database.";
		}
		
		# Grant access right of the group namespaces by default
		$privStatus = $this->addPrivileges( array($groupName), array($ns_number), array('read', 'edit') );
		if ($privStatus !== true) {
			return $privStatus;
		}
		
		return true;
	}
	
	/**
	 * Remove a new group from wiki. Also remove the associated namespaces.
	 * 
	 * @param	string	$groupName	Name of the new group
	 * @return	true if success, or a string stating the cause of failure
	 */
	private function removeGroups( $groups ) {
		global $wgAccessControlPanelAllowedGroup;
		
		$dbw = wfGetDB( DB_MASTER );
		
		if ( count($groups) == 0 ) {
			return "No groups were selected.";
		}
		
		foreach ( $groups as $grp ) {
			if ($grp == $wgAccessControlPanelAllowedGroup) {
				// Return error when trying to removing allowed group
				return "Group '$wgAccessControlPanelAllowedGroup' is not allowed to be removed by this control panel.";
			}
			
			$dbw->begin();
			
			# Delete selected groups from database
			if ( !($dbw->delete('tw_groups', array('tw_grp_name' => $grp)) ) ) {
				$dbw->rollback();
				return "Failed to delete group '$grp' from database.";
			}
	
			# Deleting respective privileges with no longer existing namespace (group) name
			$result = $dbw->selectRow('tw_namespaces', array('tw_ns_number'), array('tw_ns_name' => $grp));
			$ns_number = $result->tw_ns_number;
				
			# All privileges with the given namespace
			if ( !( $dbw->delete('tw_privileges', array('tw_ns_number' => $ns_number)) ) ) {
				$dbw->rollback();
				return "Failed to remove privileges regarding namespace associated with '$grp'. " . 
					"(Namespace number $ns_number)";
			}
				
			# And associated discussion page
			if ( !( $dbw->delete('tw_privileges', array('tw_ns_number' => $ns_number+1)) ) ) {
				$dbw->rollback();
				return "Failed to remove privileges regarding namespace associated with '$grp'. " . 
					"(Namespace number " . ($ns_number+1) . ")";
			}
				
			# Deleting all occurrences of deleting namespace name
			$result = $dbw->delete('tw_privileges', array('tw_priv_group' => $grp));
		
			# Deleting groups from 'user_groups' table
			if ( !($dbw->delete('user_groups', array('ug_group' => $grp)) ) ) {
				$dbw->rollback();
				return "Cannot remove group '$grp' from user.";
			}
		
			# Delete respective namespaces from database
			if ( !($dbw->delete('tw_namespaces', array('tw_ns_name' => $grp)) ) ) {
				$dbw->rollback();
				return "Cannot remove namespace '$grp' from database.";
			}
				
			if ( !( $dbw->delete('tw_namespaces', array('tw_ns_name' => $grp.'_talk')) ) ) {
				$dbw->rollback();
				return "Cannot remove namespace '" . $grp.'_talk' . "' from database.";
			}
			
			$dbw->commit();
		}
		
		return true;
	}
	
	/**
	 * List group members of selected group(s).
	 * 
	 * @param	$groups	Selected group(s)
	 * @return	HTML segment
	 */
	private function listGroupMembers( $groups ) {
		global $wgUser, $wgScriptPath, $wgScript;
		
		$dbr = wfGetDB( DB_SLAVE );
		
		if (count($groups) == 0) {
			return $this->getFail("No groups were selected.");
		}

		$membersOut = "";

		foreach ($groups as $grp) {
				
			$memberGroups = $dbr->select('user_groups', 'ug_user', array('ug_group' => $grp));
				
			$membersOut .= '<h3><img src="' . $wgScriptPath . '/extensions/UMEduWiki/images/groups.png"><b> Group "' . $grp . '" members</b></h3>';
			$membersOut .= '<ul>';
			
			if ($memberGroups->numRows() == 0) {
				$membersOut .= '<font color="#C0C0C0">The group has no members yet.</font>';
				$membersOut .= '</ul><br />';
				continue;
			}
			
			foreach ($memberGroups as $row) {
				$memberId = $row->ug_user;
				$memberUserName = User::whoIs($memberId);
				$memberRealName = User::whoIsReal($memberId);

				$membersOut .= '<li>';
				$membersOut .= $wgUser->getSkin()->makeLinkObj( 
					Title::makeTitle( NS_USER, $memberUserName ), 
					htmlspecialchars( $memberUserName ) 
				);
				if ($memberRealName == '')
					$membersOut .= '</li>';
				else
					$membersOut .= " (" .$memberRealName. ")</li>";
			}
				
			$membersOut .= '</ul><br />';
		}

		$membersOut .= "<a href=\"$wgScript?title={$this->getTitle()->getPrefixedDBkey()}\">Return to Access control page</a>";

		return $membersOut;
		
	}
	
	/**
	 * Add new privilege(s) for guest group(s) to access other group(s) page
	 * 
	 * @param $guestGroups	Guest group(s) in array form
	 * @param $ownerGroups	Owner of group(s) that guest can visit (array)
	 * @param $privileges	Privilege(s) granted to guest group(s)
	 */
	private function addPrivileges( $guestGroups, $ownerGroups, $privileges ) {
	
		$dbw = wfGetDB( DB_MASTER );
		
		if (count($guestGroups) == 0) {
			return "No guest groups selected.";
		}
		
		foreach ( $ownerGroups as $owner ) {
			
			$dbw->begin();
			
			foreach ($privileges as $privilege) {

				foreach ($guestGroups as $guest) {
					$result = $dbw->select('tw_privileges', array('*'), 
						array('tw_ns_number' => $owner, 'tw_privilege' => $privilege, 'tw_priv_group' => $guest));
					
					if ($result->numRows() == 0) {
						# Inserting new privilege
						if ( !( $dbw->insert('tw_privileges', array(
										'tw_ns_number'	=> $owner,   
										'tw_privilege'	=> $privilege, 
										'tw_priv_group' => $guest) ) 
								) ||
							 !( $dbw->insert('tw_privileges', array( 
							 			'tw_ns_number'	=> $owner + 1, 
							 			'tw_privilege' => $privilege, 
							 			'tw_priv_group' => $guest) )
							 	)
						   ) {
						   	$dbw->rollback();
							return "Insert new privilege failed. (Namespace number $owner)";
						}
					} 
				}
			}
			
			$dbw->commit();
		}
		
		return true;
	}
	
	/**
	 * Remove privilege(s) from guest group(s), so that the right to 
	 * access to other group(s) page is revoked
	 * 
	 * @param $guestGroups	Guest group(s) in array form
	 * @param $ownerGroups	Owner of group(s) that guest can visit (array)
	 * @param $privileges	Privilege(s) revoked
	 */
	private function removePrivileges( $guestGroups, $ownerGroups, $privileges ) {
		
		$dbw = wfGetDB( DB_MASTER );
		
		if (count($guestGroups) == 0) {
			return "No guest groups selected.";
		}
		
		foreach ( $ownerGroups as $owner ) {
			
			$dbw->begin();
			foreach ($privileges as $privilege) {
				
				foreach ($guestGroups as $guest) {
				
					$result = $dbw->select('tw_privileges', '*', array(
						'tw_ns_number' => $owner, 
						'tw_privilege' => $privilege, 
						'tw_priv_group' => $guest)
					);
	
					if ($result->numRows() != 0) {
	
						# Updating existing privilege
						if ( !( $dbw->delete('tw_privileges', array(
								'tw_priv_group' => $guest, 
								'tw_ns_number' => $owner, 
								'tw_privilege' => $privilege)) ) ||
							 !( $dbw->delete('tw_privileges', array(
								'tw_priv_group' => $guest, 
								'tw_ns_number' => $owner + 1, 
								'tw_privilege' => $privilege)) ) ) 
						{
							$dbw->rollback();
							return "Remove privilege '$privilege' failed. (Namespace number $owner)";
						}
					}
				}
			}
			
			$dbw->commit();
		}
		
		return true;
	}
	
	/**
	 * Remove privilege rule(s) from the database.
	 */
	private function removePrivilegeRules( $remove ) {
		
		$dbw = wfGetDB( DB_MASTER );
		
		if (count($remove) == 0) {
			return "Please select at least one rule from the list.";
		}
			
		$dbw->begin();
		foreach ($remove as $rm) { 
			$rm_array = explode(',', $rm);
			if ( !($dbw->delete('tw_privileges', array('tw_priv_id' => $rm_array)) ) ) {
				$dbw->rollback();
				return "Remove privilege rule failed.";
			}
		}
		$dbw->commit();
		
		return true;
	}
	
	/**
	 * Add user(s) into selected group(s), and remove user(s) from 
	 * non-selected groups.
	 * 
	 * @param $users	ID of users to be added into groups (array)
	 * @param $groups	Name of roups that users are going to join (array)
	 */
	private function addUser( $users, $groups ) {
		global $wgUser, $wgAccessControlPanelAllowedGroup;
		
		$dbw = wfGetDB( DB_MASTER );
		
		$result = $dbw->select('tw_groups', '*');
		$customGroups = array();
			
		# Added: Regarding only custom defined groups
		foreach ($result as $row) {
			$customGroups[] = $row->tw_grp_name;
		}
		
		# Selecting users
		foreach ( $users as $usr ) {

			$result = $dbw->select('user_groups', '*', array('ug_user' => $usr));

			# Creating wrapper object
			if ($result->numRows() == 0) {
				$wrapper = null;
			} else {
				$wrapper = $this->createWrapper($result, 'ug_group');
			}

			$dbw->begin();
			# Selecting group names
			foreach ( $groups as $grp ) {

				$result = $dbw->select('user_groups', '*', array('ug_user' => $usr, 'ug_group' => $grp));
				
				if ($result->numRows() == 0) {
					# Not in database
					# INSERT
					if ( !$dbw->insert('user_groups', array('ug_user' => $usr, 'ug_group' => $grp)) ) {
						$dbw->rollback();
						return "Cannot insert user '$usr' into group '$grp'.";
					}
				} else {
					# Already in database
					# NOP
					# Update flags
					if ($wrapper) $this->updateFlags($wrapper, $grp);
				}
			}
			$dbw->commit();
			
			# Deleting groups
			$dbw->begin();
			foreach ($wrapper as $entry) {
				if ($entry[0] == 0 && in_array($entry[1], $customGroups)) {
					if ($wgUser->getId() == $usr && $entry[1] == $wgAccessControlPanelAllowedGroup) {
						// Silently ignore the delete request if current user deselected the controller group
						continue;
					}
					
					if ( !$dbw->delete('user_groups', array('ug_user' => $usr, 'ug_group' => $entry[1]) ) ) {
						$dbw->rollback();
						return "Failed to remove $usr from $entry[1].";
					}
				}
			}
			$dbw->commit();
			
		} // for
		
		return true;
	}
	
	/**
	 * Main entry point of the special page
	 */
	function execute( $par ) {
		global $wgRequest, 
		       $wgOut, 
		       $wgUser, 
		       $wgNamespacePermissionLockdown, 
		       $wgAccessControlPanelAllowedGroup;

		$this->setHeaders();
		
		# My logic
		$wgUser->load();
		$userGroups = $wgUser->getGroups();
			
		if ( ! $this->checkUser($userGroups, $wgAccessControlPanelAllowedGroup) ) {
			$wgOut->addHTML( "You don't have access rights to this page." );
			return;
		}

		$output = '';
		
		$dbw = wfGetDB( DB_MASTER );

		if ($this->isPosted()) {

			$action = $_GET['action'];
			$value  = $_GET['value'];

			# Inserting new group
			if ($action == 'add' && $value == 'group') {
				$groupName = $_POST['groupName'];
				
				if ( ( $execResult = $this->addGroup( $groupName ) ) === true ) {
					$wgOut->addHTML( $this->getSuccess() );
				} else {
					$wgOut->addHTML( $this->getFail( $execResult ) );
				}
				
			} elseif ($action == 'remove' && $value == 'ask') {
				$groupsAvailable = $_POST['groupsAvailable'];
					
				# Add: 07.09.2007
				# Concernign listing of members of the groups
				# This is the place it has to be checked
				if (isset($_POST['submitMembers'])) {
					$wgOut->addHTML( $this->listGroupMembers( $groupsAvailable ) );
				}
				elseif (count($groupsAvailable) == 0) {
					$wgOut->addHTML( $this->getFail("No groups were selected.") );
				}
				else {	
					$wgOut->addHTML( $this->getSure($groupsAvailable) );
				}
					
				# Removing selected groups
			} elseif ($action == 'remove' && $value == 'group') {
				$groups = $_POST['groupsAvailable'];
				$groupsAvailable = $this->getArrayFromValues($groups);
				
				if ( ( $execResult = $this->removeGroups( $groupsAvailable ) ) === true ) {
					$wgOut->addHTML( $this->getSuccess() );
				} else {
					$wgOut->addHTML( $this->getFail( $execResult ) );
				}
				
			} elseif ($action == 'update' && $value == 'privilege') {
				# Updating privileges
				$guestGroups = $_POST['guestGroups'];
				$privileges  = $_POST['privileges'];
				$ownerGroups = $_POST['ownerGroups'];
					
				if (isset($_POST['submitAdd'])) {
					
					if ( ( $execResult = $this->addPrivileges( $guestGroups, $ownerGroups, $privileges ) ) === true ) {
						$wgOut->addHTML( $this->getSuccess() );
					} else {
						$wgOut->addHTML( $this->getFail( $execResult ) );
					}
					
				} else {
					# submitRemove was pressed
					
					if ( ( $execResult = $this->removePrivileges( $guestGroups, $ownerGroups, $privileges ) ) === true ) {
						$wgOut->addHTML( $this->getSuccess() );
					} else {
						$wgOut->addHTML( $this->getFail( $execResult ) );
					}
				}

			} else if ($action == 'remove' && $value == 'privilege') {
				$remove = $_POST['privileges'];
					
				if ( ( $execResult = $this->removePrivilegeRules( $remove ) ) === true ) {
					$wgOut->addHTML( $this->getSuccess() );
				} else {
					$wgOut->addHTML( $this->getFail( $execResult ) );
				}

			} else if ($action == 'add' && $value == 'user') {
					
				$users = $_POST['users'];
				if ( isset($_POST['groups']) ) {
					$groups = $_POST['groups'];
				} else {
					$groups = array();
				}
				
				if ( ( $execResult = $this->addUser( $users, $groups ) ) === true ) {
					$wgOut->addHTML( $this->getSuccess() );
				} else {
					$wgOut->addHTML( $this->getFail( $execResult ) );
				}
				
			} // else if
				
		} else {
			# Initial contents

			$output .= $this->getDisclaimer();
			$output .= $this->getGroupElement();
			$output .= $this->getUserElement();
			$output .= $this->getPrivilegeElement();
			
		}
			
		# Generate the acquired content
		$wgOut->addHTML( $output );
	}
	
	/* The following functions was in HTMLData.php */
	
	/**
	 * Get HTML fragment of control page disclamar. 
	 */
	private function getDisclaimer() {
		
		global $wgScriptPath, $wgEmergencyContact;
		
		$out = "
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
			<tr>
				<td>
					<img border=\"0\" src=\"$wgScriptPath/extensions/UMEduWiki/images/Lock.png\">
				</td>
				<td bgcolor=\"#F5F5F5\">
					<p style=\"margin-left: 5px; font-style: italic\">This special page is used to control access rights to custom defined groups. 
					For any questions or troubleshooting, please <a href=\"mailto:".$wgEmergencyContact."\">contact</a> the administrator.
				</td>
			</tr>
		</table> ";

		return $out;
	}
	
	/**
	 * Generate a GET-format string from an array.
	 */
	private function getValuesFromArray( $arr ) {
		$out = "";
		
		for ($i=0; $i<count($arr); $i++) {
			if ($i == count($arr)-1)
				$out .= $arr[$i];
			else
				$out .= $arr[$i]."#";
		}
		
		return $out;
	}
	
	/**
	 * Generate HTML fragment to ask for confirmation before groups 
	 * are going to be deleted.
	 * 
	 * @param $groupsAvailable	Groups that are going to be deleted.
	 */
	private function getSure( $groupsAvailable ) {
		global $wgScript, $wgScriptPath;
		
		$valuesFromArray = $this->getValuesFromArray($groupsAvailable);
		
		$out = "
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"800\">
				<tr>
					<td width=\"50\" valign=\"top\">
						<img src=\"$wgScriptPath/extensions/UMEduWiki/images/sure.png\">
					</td>
					<td valign=\"top\">
						<form method=\"POST\" action=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}&action=remove&value=group\">
							<p><b><i>Are you sure you want to delete this group?</i></b></p>
							<p>
								<input type=\"submit\" value=\"Yes\" name=\"submit\">
								<input type=\"submit\" value=\"No\" name=\"submitNo\">
								<input type=\"hidden\" value=\"".$valuesFromArray."\" name=\"groupsAvailable\">
							</p>
						</form>
					</td>
				</tr>
			</table>
		";
		
		return $out;
	}
	
	/**
	 * Get the HTML fragment for group management control.
	 */
	private function getGroupElement() {
		global $wgScript;
		
		$dbr = wfGetDB( DB_SLAVE );
		
		$out = "
		
		<fieldset style=\"padding: 2\"><legend><b>Group management</b></legend>
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"800\">
				<tr>
					<td width=\"377\" valign=\"top\">
					<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
						<tr>
							<td valign=\"top\">
								<p align=\"right\" style=\"margin-right: 5px\">New group:
							</td>
							<td>
								<form name=\"groupAdd\" action=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}&action=add&value=group\" method=\"post\">
									<input type=\"text\" name=\"groupName\" size=\"20\">
									<input type=\"submit\" value=\"Add\" name=\"submit\"><br />
									<font size=\"1\" color=\"#808080\">(e.g. Group1 or Group_1)</font>
								</form>
							</td>
						</tr>
					</table>
					</td>
					<td width=\"423\" valign=\"top\">
					<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
						<tr>
							<td width=\"118\" valign=\"top\">
							<p align=\"right\" style=\"margin-right: 5px\">Available groups:</td>
							<form name=\"groupRemove\" action=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}&action=remove&value=ask\" method=\"post\">
								<td width=\"80\">
									<select size=\"5\" name=\"groupsAvailable[]\" multiple>";
											
										/* Selecting GROUPS */
										$myGroups = $dbr->select('tw_groups', '*', array(), __METHOD__, array( 'ORDER BY' => 'tw_grp_name ASC') );

										foreach ($myGroups as $row) {
											$groupName = $row->tw_grp_name;
											$out .= "<option value=\"".$groupName."\">".$groupName."</option>";
										}
											
								$out .= "</select>
								</td>
								<td valign=\"top\">&nbsp;<input style=\"width: 10em\" type=\"submit\" value=\"Remove\" name=\"submit\"><br />
								&nbsp;<input style=\"width: 10em\" type=\"submit\" value=\"Show members\" name=\"submitMembers\">
								</td>
							</form>
						</tr>
					</table>
					</td>
				</tr>
			</table>
			<br />
			<p style=\"margin-left: 10px; font-style: italic\"><b>Tip:</b> For multiple selection press and hold CTRL and (left) click on the groups you want to select or deselect.</p>
		</fieldset>
		";
		
		return $out;
	}
	
	/**
	 * Get the HTML fragment for user management control.
	 */
	private function getUserElement() {
		global $wgScript;
	
		$dbr = wfGetDB( DB_SLAVE );
		
		$out = "
		<fieldset style=\"padding: 2\"><legend><b>User management</b></legend>
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"800\">
				<tr>
					<td valign=\"top\">
						<p align=\"right\" style=\"margin-right: 8px\">User(s)
					</td>
					<form name=\"userGroupsForm\" method=\"POST\" action=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}&action=add&value=user\">
						<td valign=\"top\" width=\"89\">
							<select size=\"10\" name=\"users[]\" multiple>";
								
								$result = $dbr->select('user', array('user_id', 'user_name'), array(), __METHOD__, array( 'ORDER BY' => 'user_name ASC' ) );
								
								foreach ($result as $row) {
									
									$user_id = $row->user_id;
									$user_name = $row->user_name;
									
									$out .= "<option value=\"".$user_id."\">".$user_name."</option>";
								}
								
							$out .= "
							</select>
						</td>
					
						<td valign=\"top\" width=\"200\">
							<p align=\"center\"> is (are) member(s) of group(s)
						</td>
					
						<td valign=\"top\" width=\"85\">
							<select size=\"10\" name=\"groups[]\" multiple>";
							
								$result = $dbr->select('tw_groups', array('tw_grp_name'), array(), __METHOD__, array( 'ORDER BY' => 'tw_grp_name ASC') );
								
								foreach ($result as $row) {
									
									$name = $row->tw_grp_name;
									
									$out .= "<option value=\"".$name."\">".$name."</option>";
								}

							$out .= "</select>
						</td>

						<td valign=\"top\">
							<p align=\"left\" style=\"margin-left: 8px\">
								<input type=\"submit\" value=\"Update\" name=\"submit\">
							</p>
						</td>
					</form>
				</tr>
			</table>
			<br />
			
			<p style=\"margin-left: 10px; font-style: italic\"><b>Tip:</b> To clear all group memberships of a user, select a user from the list and click the 'Update' button without having selected any groups.</p>
		</fieldset>
		";
		
		return $out;
	}
	
	/**
	 * Get the HTML fragment for user privilege control.
	 */
	private function getPrivilegeElement() {
		global $wgScript, $wgScriptPath;
		
		$dbr = wfGetDB( DB_SLAVE );
		
		$out = "
		<script type=\"text/javascript\">
		<!--
		    function toggle_visibility(id) {
		       var e = document.getElementById(id);
		       var l = document.getElementById('linkContent');
			   
			   if(e.style.display == 'block') {
		          e.style.display = 'none';
				  l.innerHTML = '[ + ] Show existing privileges';
		       } else {
		          e.style.display = 'block';
				  l.innerHTML = '[ - ] Hide existing privileges';
			   }
		    }
		//-->
		</script>
		
		<fieldset style=\"padding: 2\"><legend><b>Privilege management</b></legend>
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
			<tr>
				<td width=\"30px\" bgcolor=\"#F5F5F5\">
					<img src=\"$wgScriptPath/extensions/UMEduWiki/images/info.png\">
				</td>
				<td bgcolor=\"#F5F5F5\">
				<p style=\"margin-left: 5px\"><i>To add new privilege(s) or remove existing one(s)
				simply select which group(s) will have what privilege(s) over which other group(s) and then 
				click the 'Add' or 'Remove' button, respectively.</i></p></td>
			</tr>
		</table>
		<br />
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"800\">
			<tr>
				<td valign=\"top\">
					<p align=\"right\" style=\"margin-right: 8px\">Group(s)
				</td>
				<td valign=\"top\" width=\"89\">
				
				<form name=\"addPrivilegesForm\" method=\"POST\" action=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}&action=update&value=privilege\">
					<p align=\"center\" style=\"margin-right: 5px\">
					<select size=\"5\" name=\"guestGroups[]\" multiple>";
					
					/*	Add anonymous users and logged-in users as guest groups 
						to namespaces access option.
						Per request 6 Mar 2013 by Peter */
					$out .= "<option value=\"*\">(Anyone)</option>";
					$out .= "<option value=\"user\">(Logged-in users)</option>";
					
					$guestGroups = $dbr->select('tw_groups', array('tw_grp_name'), array(), __METHOD__, array( 'ORDER BY' => 'tw_grp_name ASC') );

					foreach ($guestGroups as $row) {
						$groupName = $row->tw_grp_name;
						$out .= "<option value=\"$groupName\">$groupName</option>";
					}	
					
					$out .= "</select>
					</p>
				</td>
				
				<td valign=\"top\" width=\"40\">
					<p align=\"center\">can</p>
				</td>
				
				<td valign=\"top\" width=\"68\">
					<p align=\"center\">
					<select size=\"2\" name=\"privileges[]\" multiple>
						<option value=\"read\">read</option>
						<option value=\"edit\">edit</option>
					</select>
					</p>
				</td>
				
				<td valign=\"top\" width=\"150\">
					<p align=\"center\">the pages of group(s)</p>
				</td>
				
				<td valign=\"top\" width=\"100\">
					<p align=\"center\">
					<select size=\"5\" name=\"ownerGroups[]\" multiple>";
						
					$myGroups = $dbr->select('tw_groups', array('tw_grp_name'), array(), __METHOD__, array( 'ORDER BY' => 'tw_grp_name ASC' ) );
					
					foreach ($myGroups as $row) {
						$groupName = $row->tw_grp_name;
						
						$myNamespace = $dbr->selectRow('tw_namespaces', array('tw_ns_number'), array('tw_ns_name' => $groupName));
						$ns_number = $myNamespace->tw_ns_number;
						
						$out .= "<option value=\"".$ns_number."\">".$groupName."</option>";
					}	
					
					$out .= "</select>
					</p>
				</td>

				<td valign=\"top\">
					<p align=\"left\" style=\"margin-left: 3px\">
						<input style=\"width: 6em\" type=\"submit\" value=\"Add\" name=\"submitAdd\"><br />
						<input style=\"width: 6em\" type=\"submit\" value=\"Remove\" name=\"submitRemove\">
					</p>
				</td>
				</form>
			</tr>
		</table>
		
		<p>
			<a href=\"#\" onclick=\"toggle_visibility('advancedPrivileges');\">
				<span id=\"linkContent\">
					[ + ] Show existing privileges
				</span>
			</a>
		</p>
		
		<div id=\"advancedPrivileges\" style=\"display: none\">
		
		<fieldset style=\"padding: 2\"><legend><font color=\"#808080\"><b>Existing privileges</b></font></legend>
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
				<tr>
					<td width=\"30px\" bgcolor=\"#F5F5F5\">
						<img src=\"$wgScriptPath/extensions/UMEduWiki/images/info.png\">
					</td>
					<td bgcolor=\"#F5F5F5\">
					<p style=\"margin-left: 5px\"><i>If you would like to remove one (or more) of the 
					following privileges, select the privilege(s) you want to remove and 
					click the 'Remove selected' button below.</i></p></td>
				</tr>
			</table>
			<br />
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
				<form method=\"POST\" action=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}&action=remove&value=privilege\">";
					
					$result_1 = $dbr->select('tw_privileges', array('tw_ns_number', 'tw_privilege'), array(), __METHOD__, array( 'DISTINCT', 'ORDER BY' => 'tw_ns_number ASC') );
					
					foreach ($result_1 as $row) {
						$ns_number = $row->tw_ns_number;
						$privilege = $row->tw_privilege;
						
						$result_2 = $dbr->selectRow('tw_namespaces', array('*'), array('tw_ns_number' => $ns_number));
						$ns_name = $result_2->tw_ns_name;
						
						$result_1_guest = $dbr->select('tw_privileges', array('tw_priv_id', 'tw_priv_group'), array('tw_ns_number' => $ns_number, 'tw_privilege' => $privilege));
						
						$visitors = '';
						$priv_ids = '';
						foreach($result_1_guest as $guest) {
							/*	Add anonymous users and logged-in users as guest group 
								to namespaces access option.
								Per request 6 Mar 2013 by Peter */
							$visitor = $guest->tw_priv_group;
							if ( $visitor == '*' ) {
								$visitor = '(Anyone)';
							}
							else if ( $visitor == 'user' ) {
								$visitor = '(Logged-in users)';
							}
							
							$visitors .= $visitor . ', ';
							$priv_ids .= $guest->tw_priv_id . ',';
						}
						$visitors = substr($visitors, 0, -2);
						$priv_ids = substr($priv_ids, 0, -1);
						
						$out .= "
						
						<tr>
							<td width=\"30px\">
								<p align=\"center\">
									<input type=\"checkbox\" name=\"privileges[]\" value=\"$priv_ids\" id=\"$priv_ids\">
								</p>
							</td>
						
							<td height=\"25px\">
								<label for=\"$priv_ids\">Group(s) <i><span style=\"background-color: #F3F3F3\">$visitors</span></i> can <b>$privilege</b> the pages of group <i><span style=\"background-color: #F3F3F3\">$ns_name</span></i></label>
							</td>
							
							<td width=\"200px\">&nbsp;</td>
						</tr>
						
						";
						
					}
					
					$out .= "<tr>
						<td width=\"30px\">&nbsp;</td>
						
						<td height=\"25px\">
							<input type=\"submit\" value=\"Remove selected\" name=\"submit\">
						</td>
						
						<td width=\"200px\">&nbsp;</td>
					</tr>
				</form>
		</table>
		</fieldset>
		</div>
		
		</fieldset>";	
		
		return $out;
	}

	/**
	 * Generate the success screen.
	 */
	private function getSuccess() {
		
		global $wgScript, $wgScriptPath;
		
		$out = "
			<p style=\"margin-bottom: 3px\"><font color=\"#008000\" size=\"4\"><b>Success</b></font></p>
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
				<tr>
					<td valign=\"top\" width=\"55\">
						<img border=\"0\" src=\"$wgScriptPath/extensions/UMEduWiki/images/success.png\">
					</td>
					<td>
						<p style=\"margin-left: 5px\"><b><i>The selected action has been successfully applied. Changes will be visible after you return to the previous page.</i></b><br />
						<a href=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}\">Return to Access control page</a>
					</td>
				</tr>
			</table>
		";
		
		return $out;
	}
	
	/**
	 * Generate the failure screen.
	 * 
	 * @param string $cause A description of the failure (can be empty)
	 */
	private function getFail( $cause = '' ) {

		global $wgEmergencyContact, $wgScriptPath, $wgScript;
		
		$out = "
			<p style=\"margin-bottom: 3px\"><font color=\"#BD1516\" size=\"4\"><b>Failed</b></font></p>
			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
				<tr>
					<td valign=\"top\" width=\"55\">
						<img border=\"0\" src=\"$wgScriptPath/extensions/UMEduWiki/images/fail.jpg\">
					</td>
					<td>
						<p style=\"margin-left: 5px\"><b><i>The selected action has failed to complete. ";
		
		if ( !empty($cause) ) {
			$out .= $cause;
		}
		
		$out .= " Please <a href=\"mailto:".$wgEmergencyContact."\">contact</a> the administrator and report the problem if it continues to repeat.</i></b><br />
						<a href=\"".$wgScript."?title={$this->getTitle()->getPrefixedDBkey()}\">Return to Access control page</a>
					</td>
				</tr>
			</table>
		";
		
		return $out;	
	
	}
}
?>
