<?php

namespace BlueSpice\About;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'BlueSpiceAbout' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return \Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-bluespiceabout-desc' );
	}

	/**
	 *
	 * @return \Message
	 */
	public function getName() {
		return wfMessage( 'bs-bluespiceabout-about-bluespice' );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses() {
		return [ 'icon-admin-bluespiceabout' ];
	}

	/**
	 *
	 * @return array
	 */
	public function getDataAttributes() {
		return [];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getPermissions() {
		$permissions = [
			'bluespiceabout-viewspecialpage'
		];
		return $permissions;
	}

}
