<?php

namespace BlueSpice\PageAssignments;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'ManagePageAssignments' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-pageassignments-desc' );
	}

	public function getName() {
		return wfMessage( 'managepageassignments' );
	}

	public function getClasses() {
		$classes = array(
			'bs-admin-link bs-icon-profile'
		);

		return $classes;
	}

	public function getDataAttributes() {
		return [];
	}

	public function getPermissions() {
		$permissions = array(
			'pageassignments'
		);
		return $permissions;
	}

}