<?php

namespace BlueSpice\InterWikiLinks;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'InterWikiLinks' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-interwikilinks-desc' );
	}

	public function getName() {
		return wfMessage( 'bs-interwikilinks-label' );
	}

	public function getClasses() {
		$classes = array(
			'bs-icon-chain'
		);

		return $classes;
	}

	public function getDataAttributes() {
		return [];
	}

	public function getPermissions() {
		$permissions = array(
			'interwikilinks-viewspecialpage'
		);
		return $permissions;
	}

}