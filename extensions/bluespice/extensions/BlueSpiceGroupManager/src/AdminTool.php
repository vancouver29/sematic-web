<?php

namespace BlueSpice\GroupManager;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'GroupManager' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-groupmanager-desc' );
	}

	public function getName() {
		return wfMessage( 'groupmanager' );
	}

	public function getClasses() {
		$classes = array(
			'bs-icon-group'
		);

		return $classes;
	}

	public function getDataAttributes() {
		return [];
	}

	public function getPermissions() {
		$permissions = array(
			'groupmanager-viewspecialpage'
		);
		return $permissions;
	}

}