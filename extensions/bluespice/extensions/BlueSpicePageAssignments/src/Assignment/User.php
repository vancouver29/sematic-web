<?php

namespace BlueSpice\PageAssignments\Assignment;

use BlueSpice\Services;

class User extends \BlueSpice\PageAssignments\Assignment {

	protected function makeAnchor() {
		return $this->linkRenderer->makeLink(
			$this->getUser()->getUserPage(),
			new \HtmlArmor( $this->getText() )
		);
	}

	public function getText() {
		$utilities = Services::getInstance()->getBSUtilityFactory();
		return $utilities->getUserHelper( $this->getUser() )->getDisplayName();
	}

	public function getUserIds() {
		return [ $this->getUser()->getId() ];
	}

	/**
	 *
	 * @return \User
	 */
	protected function getUser() {
		return \User::newFromName( $this->getKey() );
	}

}