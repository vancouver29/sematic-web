<?php

namespace BlueSpice\NamespaceCSS;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'BlueSpiceNamespaceCSSManager' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-namespacecss-desc' );
	}

	public function getName() {
		return wfMessage( 'bluespicenamespacecssmanager' );
	}

	public function getClasses() {
		return [
			'bs-icon-painting-roll'
		];
	}

	public function getDataAttributes() {
		return [];
	}

	public function getPermissions() {
		return [
			'editinterface'
		];
	}

}